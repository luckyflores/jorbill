<?php

namespace App\Filament\Resources\Naps\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('splitter'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                TextInput::make('ports_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('parent_nap_id')
                    ->numeric(),
                TextInput::make('olt_id')
                    ->numeric(),
                TextInput::make('pon_port'),
                Textarea::make('address')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
