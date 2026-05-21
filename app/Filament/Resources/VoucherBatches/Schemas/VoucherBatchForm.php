<?php

namespace App\Filament\Resources\VoucherBatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VoucherBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code_prefix'),
                TextInput::make('count')
                    ->required()
                    ->numeric(),
                TextInput::make('value_centavos')
                    ->numeric(),
                TextInput::make('duration_minutes')
                    ->numeric(),
                DateTimePicker::make('expires_at'),
                TextInput::make('service_id')
                    ->numeric(),
                TextInput::make('created_by_user_id')
                    ->numeric(),
            ]);
    }
}
