<?php

namespace App\Filament\Resources\CustomerDiagnostics\Pages;

use App\Filament\Resources\CustomerDiagnostics\CustomerDiagnosticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerDiagnostics extends ListRecords
{
    protected static string $resource = CustomerDiagnosticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
