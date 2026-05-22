<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\PaymentRecorded;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\BirNumbering;
use App\Services\Payment\Contracts\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HitpayWebhookController
{
    public function handle(Request $request, PaymentGateway $gateway, BirNumbering $numbering)
    {
        $payload = $request->all();
        Log::info('HitpayWebhookController: incoming', ['payload_keys' => array_keys($payload)]);

        $normalized = $gateway->handleWebhook($payload);
        if (! $normalized) {
            return response()->json(['ok' => true, 'note' => 'invalid signature or missing fields'], 200);
        }

        if ($normalized['status'] !== 'completed') {
            return response()->json(['ok' => true, 'note' => "status={$normalized['status']}, not recording yet"], 200);
        }

        $invoice = Invoice::where('invoice_number', $normalized['reference_number'])->first();
        if (! $invoice) {
            Log::warning('HitpayWebhookController: invoice not found', ['ref' => $normalized['reference_number']]);
            return response()->json(['ok' => true, 'note' => 'invoice not found'], 200);
        }

        // Idempotent — if a Payment with this gateway_reference already exists, do not create a duplicate
        $reference = $normalized['payment_id'] ?: $normalized['gateway_reference'];
        if (Payment::where('gateway_reference', $reference)->exists()) {
            return response()->json(['ok' => true, 'note' => 'already recorded'], 200);
        }

        $payment = DB::transaction(function () use ($invoice, $normalized, $numbering, $reference) {
            $payment = Payment::create([
                'payment_number'    => $numbering->nextPaymentNumber(),
                'customer_id'       => $invoice->customer_id,
                'invoice_id'        => $invoice->id,
                'amount_centavos'   => $normalized['amount_centavos'],
                'gateway'           => 'hitpay',
                'gateway_reference' => $reference,
                'received_at'       => now(),
                'status'            => 'completed',
            ]);

            // Update the invoice's running paid total + status
            $newPaid = ($invoice->amount_paid_centavos ?? 0) + $payment->amount_centavos;
            $invoice->update([
                'amount_paid_centavos' => $newPaid,
                'status' => $newPaid >= $invoice->total_centavos ? 'paid' : $invoice->status,
            ]);

            return $payment;
        });

        // Fires the listener chain (SendPaymentReceipt SMS + any automation rules)
        event(new PaymentRecorded($payment));

        return response()->json(['ok' => true, 'payment_id' => $payment->id]);
    }
}
