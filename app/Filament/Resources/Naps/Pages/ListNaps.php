<?php

namespace App\Filament\Resources\Naps\Pages;

use App\Filament\Resources\Naps\NapResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNaps extends ListRecords
{
    protected static string $resource = NapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
