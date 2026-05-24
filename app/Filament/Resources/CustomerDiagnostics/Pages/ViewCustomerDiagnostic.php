<?php

namespace App\Filament\Resources\CustomerDiagnostics\Pages;

use App\Filament\Resources\CustomerDiagnostics\CustomerDiagnosticResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerDiagnostic extends ViewRecord
{
    protected static string $resource = CustomerDiagnosticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
