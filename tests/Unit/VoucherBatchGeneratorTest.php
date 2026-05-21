<?php

namespace Tests\Unit;

use App\Models\VoucherBatch;
use App\Services\Vouchers\VoucherBatchGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherBatchGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_requested_number_of_unique_vouchers(): void
    {
        $batch = VoucherBatch::create([
            'name' => 'Test batch', 'code_prefix' => 'TST',
            'count' => 25, 'duration_minutes' => 60,
        ]);
        $created = app(VoucherBatchGenerator::class)->generate($batch);
        $this->assertSame(25, $created);
        $this->assertSame(25, $batch->vouchers()->count());
        $this->assertSame(25, $batch->vouchers()->distinct('code')->count('code'));
    }

    public function test_generate_is_idempotent(): void
    {
        $batch = VoucherBatch::create(['name' => 'Test', 'count' => 10, 'duration_minutes' => 60]);
        app(VoucherBatchGenerator::class)->generate($batch);
        $second = app(VoucherBatchGenerator::class)->generate($batch);
        $this->assertSame(0, $second);
        $this->assertSame(10, $batch->vouchers()->count());
    }
}
