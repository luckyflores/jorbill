<?php

namespace App\Filament\Resources\Routers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RouterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('location'),
                TextInput::make('vendor')
                    ->required()
                    ->default('mikrotik'),
                TextInput::make('model'),
                TextInput::make('ip_address')
                    ->required(),
                TextInput::make('api_port')
                    ->required()
                    ->numeric()
                    ->default(8728),
                TextInput::make('api_user')
                    ->required(),
                TextInput::make('api_password')
                    ->password()
                    ->required(),
                TextInput::make('ssh_port')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('last_seen_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
