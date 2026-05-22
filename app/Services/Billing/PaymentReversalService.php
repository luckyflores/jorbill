<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;

class PaymentReversalService
{
    /**
     * Atomically reverse a payment:
     *  1. Create offsetting Payment (negative amount, status=reversal, links to original)
     *  2. Mark original status=reversed + reversed_at + reversed_reason
     *  3. Recompute the linked Invoice's amount_paid_centavos + status from scratch
     *  4. Log activity
     */
    public function reverse(Payment $original, string $reason, ?string $notes = null): Payment
    {
        if (in_array($original->status, ['reversed', 'reversal'], true)) {
            throw new DomainException("Payment {$original->payment_number} is already reversed (status={$original->status}).");
        }
        if (! in_array($original->status, ['completed', 'pending'], true)) {
            throw new DomainException("Payment {$original->payment_number} cannot be reversed (status={$original->status}).");
        }

        return DB::transaction(function () use ($original, $reason, $notes) {
            $fullReason = $notes ? "{$reason}: {$notes}" : $reason;

            // 1. Create the reversal entry — negative amount
            $reversal = Payment::create([
                'payment_number'    => 'REV-' . $original->payment_number,
                'customer_id'       => $original->customer_id,
                'invoice_id'        => $original->invoice_id,
                'amount_centavos'   => -1 * (int) $original->amount_centavos,
                'gateway'           => $original->gateway,
                'gateway_reference' => $original->gateway_reference ? 'REV-' . $original->gateway_reference : null,
                'received_at'       => now(),
                'status'            => 'reversal',
                'reverses_payment_id' => $original->id,
                'reversed_reason'   => $fullReason,
            ]);

            // 2. Mark the original
            $original->forceFill([
                'status'           => 'reversed',
                'reversed_at'      => now(),
                'reversed_reason'  => $fullReason,
            ])->save();

            // 3. Recompute the invoice from scratch
            if ($original->invoice_id) {
                $this->recomputeInvoice($original->invoice_id);
            }

            // 4. Audit log
            activity('payments')
                ->performedOn($original)
                ->withProperties([
                    'reversal_payment_id' => $reversal->id,
                    'original_amount'     => $original->amount_centavos,
                    'reason'              => $fullReason,
                ])
                ->log("Payment {$original->payment_number} reversed: {$fullReason}");

            return $reversal;
        });
    }

    /**
     * Recompute amount_paid_centavos + status of an Invoice from all its non-failed,
     * non-reversed Payment rows (treats the reversal Payment's negative amount naturally).
     */
    public function recomputeInvoice(int $invoiceId): void
    {
        $invoice = Invoice::find($invoiceId);
        if (! $invoice) return;

        // Sum: completed payments + reversal payments (negative). Skip 'reversed' (the original got offset).
        $paid = (int) Payment::where('invoice_id', $invoiceId)
            ->whereIn('status', ['completed', 'reversal'])
            ->sum('amount_centavos');

        $paid = max(0, $paid);

        $newStatus = match (true) {
            $paid >= $invoice->total_centavos          => 'paid',
            $invoice->due_at && $invoice->due_at->isPast() => 'overdue',
            default                                     => 'issued',
        };

        $invoice->update([
            'amount_paid_centavos' => $paid,
            'status'               => $newStatus,
        ]);
    }
}
