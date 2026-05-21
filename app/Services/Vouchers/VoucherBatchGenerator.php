<?php

namespace App\Services\Vouchers;

use App\Models\Voucher;
use App\Models\VoucherBatch;

class VoucherBatchGenerator
{
    public function generate(VoucherBatch $batch): int
    {
        $existing = $batch->vouchers()->count();
        $toCreate = max(0, $batch->count - $existing);
        if ($toCreate === 0) return 0;

        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $rows = [];
        for ($i = 0; $i < $toCreate; $i++) {
            $code = ($batch->code_prefix ?? '')
                . substr(str_shuffle(str_repeat($alphabet, 4)), 0, 10);
            $rows[] = [
                'code'             => $code,
                'batch_id'         => $batch->id,
                'value_centavos'   => $batch->value_centavos,
                'duration_minutes' => $batch->duration_minutes,
                'expires_at'       => $batch->expires_at,
                'status'           => 'unused',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }
        Voucher::insert($rows);
        return $toCreate;
    }
}
