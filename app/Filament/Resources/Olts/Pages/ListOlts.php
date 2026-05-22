<?php

namespace App\Filament\Resources\Olts\Pages;

use App\Filament\Resources\Olts\OltResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOlts extends ListRecords
{
    protected static string $resource = OltResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
