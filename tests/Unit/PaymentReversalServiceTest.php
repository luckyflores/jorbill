<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\PaymentReversalService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReversalServiceTest extends TestCase
{
    use RefreshDatabase;

    private function setup_invoice_with_payment(int $invoiceTotal = 99900, int $paid = 99900, ?int $daysOverdue = null): array
    {
        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $due = $daysOverdue !== null ? now()->subDays($daysOverdue) : now()->addDays(10);
        $invoice = Invoice::create([
            'invoice_number'       => 'SI-TEST-' . uniqid(),
            'series_code'          => 'SI',
            'customer_id'          => $customer->id,
            'issued_at'            => now()->subDays(20),
            'due_at'               => $due,
            'subtotal_centavos'    => $invoiceTotal,
            'vat_centavos'         => 0,
            'total_centavos'       => $invoiceTotal,
            'amount_paid_centavos' => $paid,
            'status'               => $paid >= $invoiceTotal ? 'paid' : 'issued',
        ]);
        $payment = Payment::create([
            'payment_number'   => 'PMT-T-' . uniqid(),
            'customer_id'      => $customer->id,
            'invoice_id'       => $invoice->id,
            'amount_centavos'  => $paid,
            'gateway'          => 'cash',
            'received_at'      => now()->subDays(1),
            'status'           => 'completed',
        ]);
        return [$customer, $invoice, $payment];
    }

    public function test_reverse_creates_offsetting_payment_with_negative_amount(): void
    {
        [, , $payment] = $this->setup_invoice_with_payment();
        $reversal = app(PaymentReversalService::class)->reverse($payment, 'bounced_check');

        $this->assertSame(-99900, (int) $reversal->amount_centavos);
        $this->assertSame('reversal', $reversal->status);
        $this->assertSame($payment->id, $reversal->reverses_payment_id);
    }

    public function test_reverse_marks_original_as_reversed_with_reason(): void
    {
        [, , $payment] = $this->setup_invoice_with_payment();
        app(PaymentReversalService::class)->reverse($payment, 'duplicate', 'staff posted twice');
        $payment->refresh();

        $this->assertSame('reversed', $payment->status);
        $this->assertNotNull($payment->reversed_at);
        $this->assertSame('duplicate: staff posted twice', $payment->reversed_reason);
    }

    public function test_invoice_status_goes_back_to_issued_when_due_in_future(): void
    {
        [, $invoice, $payment] = $this->setup_invoice_with_payment();
        app(PaymentReversalService::class)->reverse($payment, 'refund');
        $invoice->refresh();

        $this->assertSame(0, (int) $invoice->amount_paid_centavos);
        $this->assertSame('issued', $invoice->status);
    }

    public function test_invoice_status_goes_back_to_overdue_when_past_due(): void
    {
        [, $invoice, $payment] = $this->setup_invoice_with_payment(daysOverdue: 30);
        app(PaymentReversalService::class)->reverse($payment, 'bounced_check');
        $invoice->refresh();

        $this->assertSame(0, (int) $invoice->amount_paid_centavos);
        $this->assertSame('overdue', $invoice->status);
    }

    public function test_cannot_reverse_already_reversed_payment(): void
    {
        [, , $payment] = $this->setup_invoice_with_payment();
        app(PaymentReversalService::class)->reverse($payment, 'refund');
        $payment->refresh();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/already reversed/');
        app(PaymentReversalService::class)->reverse($payment, 'refund');
    }

    public function test_cannot_reverse_a_reversal_entry(): void
    {
        [, , $payment] = $this->setup_invoice_with_payment();
        $reversal = app(PaymentReversalService::class)->reverse($payment, 'refund');

        $this->expectException(DomainException::class);
        app(PaymentReversalService::class)->reverse($reversal, 'whatever');
    }

    public function test_partial_reversal_drops_paid_amount_correctly(): void
    {
        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $invoice = Invoice::create([
            'invoice_number' => 'SI-PARTIAL', 'series_code' => 'SI',
            'customer_id' => $customer->id,
            'issued_at' => now()->subDays(20),
            'due_at' => now()->addDays(10),
            'subtotal_centavos' => 100000, 'vat_centavos' => 0, 'total_centavos' => 100000,
            'amount_paid_centavos' => 100000, 'status' => 'paid',
        ]);
        $p1 = Payment::create([
            'payment_number' => 'P1', 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'amount_centavos' => 50000, 'gateway' => 'cash', 'received_at' => now(), 'status' => 'completed',
        ]);
        Payment::create([
            'payment_number' => 'P2', 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'amount_centavos' => 50000, 'gateway' => 'cash', 'received_at' => now(), 'status' => 'completed',
        ]);

        app(PaymentReversalService::class)->reverse($p1, 'duplicate');
        $invoice->refresh();

        $this->assertSame(50000, (int) $invoice->amount_paid_centavos);
        $this->assertSame('issued', $invoice->status);
    }
}
