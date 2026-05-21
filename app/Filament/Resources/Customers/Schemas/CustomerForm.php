<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_code'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('alt_phone')
                    ->tel(),
                TextInput::make('address_line1')
                    ->required(),
                TextInput::make('barangay'),
                TextInput::make('city')
                    ->required(),
                TextInput::make('province')
                    ->required(),
                TextInput::make('postal_code'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('prospect'),
                TextInput::make('tax_id'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('agent_id')
                    ->numeric(),
                DateTimePicker::make('activated_at'),
            ]);
    }
}
