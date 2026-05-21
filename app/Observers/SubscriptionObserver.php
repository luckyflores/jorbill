<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Subscription;
use App\Services\Notifications\Contracts\Notifier;

class SubscriptionObserver
{
    public function __construct(private readonly Notifier $notifier) {}

    public function updated(Subscription $subscription): void
    {
        if (! $subscription->wasChanged('status')) return;

        $customer = Customer::find($subscription->customer_id);
        if (! $customer || ! $customer->phone) return;

        $body = match ($subscription->status) {
            'active'    => "Hi {$customer->name}, your service is now ACTIVE. Enjoy!",
            'suspended' => "Hi {$customer->name}, your service has been SUSPENDED. Please settle your account.",
            'cancelled' => "Hi {$customer->name}, your service has been CANCELLED. We're sad to see you go.",
            default     => null,
        };
        if (! $body) return;

        $log = NotificationLog::create([
            'channel' => 'sms', 'driver' => $this->notifier->id(),
            'to' => $customer->phone, 'body' => $body,
            'event' => 'subscription.' . $subscription->status,
            'customer_id' => $customer->id, 'status' => 'queued',
        ]);

        try {
            $ref = $this->notifier->send($customer->phone, $body, ['sub_id' => $subscription->id]);
            $log->update(['status' => 'sent', 'gateway_reference' => $ref, 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}
