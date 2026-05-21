<?php

namespace App\Filament\Resources\PisoRates\Pages;

use App\Filament\Resources\PisoRates\PisoRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPisoRates extends ListRecords
{
    protected static string $resource = PisoRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
