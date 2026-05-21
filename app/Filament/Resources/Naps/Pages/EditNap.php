<?php

namespace App\Filament\Resources\Naps\Pages;

use App\Filament\Resources\Naps\NapResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNap extends EditRecord
{
    protected static string $resource = NapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
