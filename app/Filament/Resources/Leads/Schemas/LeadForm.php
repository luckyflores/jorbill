<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('source')
                    ->required()
                    ->default('other'),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
                TextInput::make('assigned_to')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('converted_customer_id')
                    ->numeric(),
                DateTimePicker::make('contacted_at'),
                DateTimePicker::make('converted_at'),
            ]);
    }
}
