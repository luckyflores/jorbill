<?php

namespace App\Filament\Resources\Onus\Pages;

use App\Filament\Resources\Onus\OnuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOnu extends EditRecord
{
    protected static string $resource = OnuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
