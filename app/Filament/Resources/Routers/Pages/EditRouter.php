<?php

namespace App\Filament\Resources\Routers\Pages;

use App\Filament\Resources\Routers\RouterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRouter extends EditRecord
{
    protected static string $resource = RouterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
