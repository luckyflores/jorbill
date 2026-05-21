<?php

namespace Tests\Unit;

use App\Services\Billing\BirNumbering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_numbers_increment_atomically(): void
    {
        $svc = app(BirNumbering::class);

        $first = $svc->nextInvoiceNumber();
        $second = $svc->nextInvoiceNumber();

        $year = date('Y');
        $this->assertSame("SI-{$year}-00001", $first);
        $this->assertSame("SI-{$year}-00002", $second);
    }

    public function test_different_series_have_independent_counters(): void
    {
        $svc = app(BirNumbering::class);

        $inv = $svc->nextInvoiceNumber();
        $pmt = $svc->nextPaymentNumber();
        $or  = $svc->nextReceiptNumber();

        $year = date('Y');
        $this->assertStringStartsWith("SI-{$year}-", $inv);
        $this->assertStringStartsWith("PMT-{$year}-", $pmt);
        $this->assertStringStartsWith("OR-{$year}-", $or);
    }
}
