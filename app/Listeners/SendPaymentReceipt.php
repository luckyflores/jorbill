<?php

namespace App\Listeners;

use App\Events\PaymentRecorded;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Services\Notifications\Contracts\Notifier;
use Throwable;

class SendPaymentReceipt
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(PaymentRecorded $event): void
    {
        $payment = $event->payment;
        $customer = Customer::find($payment->customer_id);
        if (! $customer || ! $customer->phone) return;

        $body = sprintf(
            'Hi %s, we received your payment of ₱%s (ref: %s). Thank you!',
            explode(' ', $customer->name)[0],
            number_format($payment->amount_centavos / 100, 2),
            $payment->payment_number,
        );

        $log = NotificationLog::create([
            'channel'     => 'sms',
            'driver'      => $this->notifier->id(),
            'to'          => $customer->phone,
            'body'        => $body,
            'event'       => 'payment.recorded',
            'customer_id' => $customer->id,
            'status'      => 'queued',
        ]);

        try {
            $ref = $this->notifier->send($customer->phone, $body, ['payment_id' => $payment->id]);
            $log->update(['status' => 'sent', 'gateway_reference' => $ref, 'sent_at' => now()]);
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}
