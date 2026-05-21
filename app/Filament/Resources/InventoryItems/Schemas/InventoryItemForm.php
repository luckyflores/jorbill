<?php

namespace App\Filament\Resources\InventoryItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('serial_number'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('unit_cost_centavos')
                    ->numeric(),
                TextInput::make('location'),
                TextInput::make('assigned_to')
                    ->numeric(),
                TextInput::make('subscription_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('in_stock'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
