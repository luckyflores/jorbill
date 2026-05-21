<?php

namespace App\Filament\Resources\VoucherBatches\Pages;

use App\Filament\Resources\VoucherBatches\VoucherBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVoucherBatches extends ListRecords
{
    protected static string $resource = VoucherBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
