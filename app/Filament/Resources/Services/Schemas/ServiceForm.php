<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('bandwidth_down_kbps')
                    ->required()
                    ->numeric(),
                TextInput::make('bandwidth_up_kbps')
                    ->required()
                    ->numeric(),
                TextInput::make('price_centavos')
                    ->required()
                    ->numeric(),
                Toggle::make('vat_inclusive')
                    ->required(),
                TextInput::make('billing_cycle')
                    ->required()
                    ->default('monthly'),
                TextInput::make('prepaid_days')
                    ->numeric(),
                TextInput::make('mikrotik_profile_name'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
