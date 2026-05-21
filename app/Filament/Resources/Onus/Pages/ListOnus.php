<?php

namespace App\Filament\Resources\Onus\Pages;

use App\Filament\Resources\Onus\OnuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOnus extends ListRecords
{
    protected static string $resource = OnuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
