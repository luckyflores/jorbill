<?php

namespace App\Services\Automation;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Services\Notifications\Contracts\Notifier;
use App\Services\Notifications\NotifierRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActionRunner
{
    public function __construct(
        private readonly Notifier $defaultNotifier,
        private readonly Interpolator $interpolator,
        private readonly NotifierRegistry $registry,
    ) {}

    public function run(array $action, array $context, Model $triggerModel, bool $dryRun = false): array
    {
        $type = $action['type'] ?? 'noop';
        try {
            $result = match ($type) {
                'send_sms'         => $this->runSendSms($action, $context, $dryRun),
                'send_template'    => $this->runSendTemplate($action, $context, $dryRun),
                'update_field'     => $this->runUpdateField($action, $context, $triggerModel, $dryRun),
                'create_job_order' => $this->runCreateJobOrder($action, $context, $dryRun),
                'log_activity'     => $this->runLogActivity($action, $context, $dryRun),
                default            => ['ok' => false, 'detail' => "unknown action type: {$type}"],
            };
            return array_merge(['type' => $type], $result);
        } catch (\Throwable $e) {
            Log::error('ActionRunner::run threw', ['type' => $type, 'error' => $e->getMessage()]);
            return ['type' => $type, 'ok' => false, 'detail' => $e->getMessage()];
        }
    }

    /** Inline-body SMS; optional channel override. */
    private function runSendSms(array $action, array $context, bool $dryRun): array
    {
        $to   = $this->interpolator->render($action['to']   ?? '{{customer.phone}}', $context);
        $body = $this->interpolator->render($action['body'] ?? '', $context);
        if (! $to || ! $body) return ['ok' => false, 'detail' => 'missing to or body'];

        $notifier = $this->resolveNotifier($action['channel'] ?? null);
        if (! $notifier) return ['ok' => false, 'detail' => "channel not registered: " . ($action['channel'] ?? '(default)')];

        if ($dryRun) return ['ok' => true, 'detail' => "[dry-run] {$notifier->id()} → {$to}: {$body}"];

        return $this->dispatchAndLog($notifier, $to, $body, 'automation', data_get($context, 'customer.id'));
    }

    /** Multi-channel template send — matches taoki's Events workflow. */
    private function runSendTemplate(array $action, array $context, bool $dryRun): array
    {
        $name = $action['template'] ?? null;
        if (! $name) return ['ok' => false, 'detail' => 'missing template name'];

        $template = NotificationTemplate::where('name', $name)->where('is_active', true)->first();
        if (! $template) return ['ok' => false, 'detail' => "template '{$name}' not found or inactive"];

        $body = $this->interpolator->render($template->body, $context);
        $to   = $this->interpolator->render($action['to'] ?? '{{customer.phone}}', $context);
        if (! $to || ! $body) return ['ok' => false, 'detail' => 'empty to/body after interpolation'];

        // Channel resolution: action.channels (array) > action.channel (single) > template.channel
        $channels = $action['channels'] ?? null;
        if (! $channels) {
            $channels = [$action['channel'] ?? $template->channel];
        }
        if (! is_array($channels)) $channels = [$channels];
        $channels = array_filter(array_map('strval', $channels));

        if ($dryRun) {
            return ['ok' => true, 'detail' => "[dry-run] template '{$name}' via [" . implode(',', $channels) . "] → {$to}: " . substr($body, 0, 80)];
        }

        $allOk = true;
        $details = [];
        foreach ($channels as $channel) {
            $notifier = $this->registry->forChannel($channel);
            if (! $notifier) {
                $details[] = "{$channel}: not registered";
                $allOk = false;
                continue;
            }
            $r = $this->dispatchAndLog($notifier, $to, $body, "template:{$name}", data_get($context, 'customer.id'));
            $details[] = "{$channel}: " . ($r['ok'] ? 'ok' : 'failed') . (isset($r['detail']) ? " ({$r['detail']})" : '');
            $allOk = $allOk && $r['ok'];
        }

        // bump template usage stats
        $template->forceFill([
            'use_count'    => ($template->use_count ?? 0) + 1,
            'last_used_at' => now(),
        ])->save();

        return ['ok' => $allOk, 'detail' => implode('; ', $details)];
    }

    private function dispatchAndLog(Notifier $notifier, string $to, string $body, string $event, ?int $customerId): array
    {
        $log = NotificationLog::create([
            'channel'     => 'sms',
            'driver'      => $notifier->id(),
            'to'          => $to,
            'body'        => $body,
            'event'       => $event,
            'customer_id' => $customerId,
            'status'      => 'queued',
        ]);
        try {
            $ref = $notifier->send($to, $body, ['source' => $event]);
            $log->update(['status' => 'sent', 'gateway_reference' => $ref, 'sent_at' => now()]);
            return ['ok' => true, 'detail' => "ref={$ref}"];
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            return ['ok' => false, 'detail' => $e->getMessage()];
        }
    }

    private function resolveNotifier(?string $channel): ?Notifier
    {
        if (! $channel) return $this->defaultNotifier;
        return $this->registry->forChannel($channel);
    }

    private function runUpdateField(array $action, array $context, Model $triggerModel, bool $dryRun): array
    {
        $target = $action['target'] ?? null;
        $value  = $this->interpolator->render((string) ($action['value'] ?? ''), $context);
        if (! $target) return ['ok' => false, 'detail' => 'missing target'];

        $parts = explode('.', $target);
        if (count($parts) === 1) {
            $field = $parts[0];
            if ($dryRun) return ['ok' => true, 'detail' => "[dry-run] trigger.{$field}={$value}"];
            $triggerModel->{$field} = $value;
            $triggerModel->save();
            return ['ok' => true, 'detail' => "trigger.{$field}={$value}"];
        }

        $relatedClass = $this->guessRelatedClass($parts[0]);
        $relatedId = data_get($context, $parts[0] . '.id');
        if (! $relatedClass || ! $relatedId) return ['ok' => false, 'detail' => "cannot resolve {$parts[0]}"];
        if ($dryRun) return ['ok' => true, 'detail' => "[dry-run] {$target}={$value}"];

        $related = $relatedClass::find($relatedId);
        if (! $related) return ['ok' => false, 'detail' => 'related not found'];
        $related->{$parts[1]} = $value;
        $related->save();
        return ['ok' => true, 'detail' => "{$target}={$value}"];
    }

    private function guessRelatedClass(string $key): ?string
    {
        return match ($key) {
            'customer'     => Customer::class,
            'subscription' => \App\Models\Subscription::class,
            'invoice'      => \App\Models\Invoice::class,
            'service'      => \App\Models\Service::class,
            default        => null,
        };
    }

    private function runCreateJobOrder(array $action, array $context, bool $dryRun): array
    {
        $description = $this->interpolator->render($action['description'] ?? 'Auto-generated', $context);
        $type        = $action['job_type'] ?? 'repair';
        $priority    = $action['priority'] ?? 'normal';
        $customerId  = (int) $this->interpolator->render((string) ($action['customer_id'] ?? '{{customer.id}}'), $context);

        if ($dryRun) return ['ok' => true, 'detail' => "[dry-run] JO type={$type} for customer #{$customerId}"];

        $jo = JobOrder::create([
            'job_number'   => 'JO-AUTO-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'type'         => $type,
            'priority'     => $priority,
            'status'       => 'pending',
            'customer_id'  => $customerId ?: null,
            'description'  => $description,
            'scheduled_at' => now()->addDay(),
        ]);
        return ['ok' => true, 'detail' => "JO #{$jo->id}"];
    }

    private function runLogActivity(array $action, array $context, bool $dryRun): array
    {
        $description = $this->interpolator->render($action['description'] ?? 'automation', $context);
        if ($dryRun) return ['ok' => true, 'detail' => "[dry-run] {$description}"];
        activity('automation')->log($description);
        return ['ok' => true, 'detail' => $description];
    }
}
