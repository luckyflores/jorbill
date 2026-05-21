<?php

namespace App\Services\Billing;

use App\Models\BirCounter;
use Illuminate\Support\Facades\DB;

class BirNumbering
{
    /** Atomic, gap-free counter for the given BIR ATP series. */
    public function next(string $series): string
    {
        return DB::transaction(function () use ($series) {
            $counter = BirCounter::query()
                ->where('series', $series)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = BirCounter::create(['series' => $series, 'next_value' => 1]);
            }

            $value = $counter->next_value;
            $counter->next_value = $value + 1;
            $counter->save();

            return sprintf('%s-%s-%05d', $series, date('Y'), $value);
        });
    }

    public function nextInvoiceNumber(): string
    {
        return $this->next('SI');
    }

    public function nextPaymentNumber(): string
    {
        return $this->next('PMT');
    }

    public function nextReceiptNumber(): string
    {
        return $this->next('OR');
    }
}
