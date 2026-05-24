<?php

namespace App\Filament\Resources\CustomerDiagnostics\Pages;

use App\Filament\Resources\CustomerDiagnostics\CustomerDiagnosticResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerDiagnostic extends CreateRecord
{
    protected static string $resource = CustomerDiagnosticResource::class;
}
