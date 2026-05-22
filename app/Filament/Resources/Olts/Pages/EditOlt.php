<?php

namespace App\Filament\Resources\Olts\Pages;

use App\Filament\Resources\Olts\OltResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOlt extends EditRecord
{
    protected static string $resource = OltResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
