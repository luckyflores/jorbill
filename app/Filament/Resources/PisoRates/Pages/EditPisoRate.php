<?php

namespace App\Filament\Resources\PisoRates\Pages;

use App\Filament\Resources\PisoRates\PisoRateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPisoRate extends EditRecord
{
    protected static string $resource = PisoRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
