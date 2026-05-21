<?php

namespace App\Filament\Resources\VoucherBatches\Pages;

use App\Filament\Resources\VoucherBatches\VoucherBatchResource;
use App\Services\Vouchers\VoucherBatchGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucherBatch extends CreateRecord
{
    protected function afterCreate(): void
    {
        app(VoucherBatchGenerator::class)->generate($this->record);
    }

    protected static string $resource = VoucherBatchResource::class;
}
