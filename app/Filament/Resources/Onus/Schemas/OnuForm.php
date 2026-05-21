<?php

namespace App\Filament\Resources\Onus\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OnuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('serial_number')
                    ->required(),
                TextInput::make('vendor')
                    ->required(),
                TextInput::make('model_name'),
                TextInput::make('mac_address'),
                TextInput::make('subscription_id')
                    ->numeric(),
                TextInput::make('nap_id')
                    ->numeric(),
                TextInput::make('nap_port')
                    ->numeric(),
                TextInput::make('rx_power_dbm')
                    ->numeric(),
                TextInput::make('tx_power_dbm')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('in_stock'),
                DateTimePicker::make('installed_at'),
                DateTimePicker::make('last_seen_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
