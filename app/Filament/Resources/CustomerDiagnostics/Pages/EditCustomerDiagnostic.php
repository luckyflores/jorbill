<?php

namespace App\Filament\Resources\CustomerDiagnostics\Pages;

use App\Filament\Resources\CustomerDiagnostics\CustomerDiagnosticResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerDiagnostic extends EditRecord
{
    protected static string $resource = CustomerDiagnosticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
