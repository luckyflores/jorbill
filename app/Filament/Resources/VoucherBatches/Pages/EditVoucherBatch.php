<?php

namespace App\Filament\Resources\VoucherBatches\Pages;

use App\Filament\Resources\VoucherBatches\VoucherBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVoucherBatch extends EditRecord
{
    protected static string $resource = VoucherBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
