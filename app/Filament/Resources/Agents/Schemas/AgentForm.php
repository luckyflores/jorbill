<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('agent_code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('commission_type')
                    ->required()
                    ->default('percentage'),
                TextInput::make('commission_percentage')
                    ->numeric(),
                TextInput::make('commission_flat_centavos')
                    ->numeric(),
                TextInput::make('bank_name'),
                TextInput::make('bank_account'),
                TextInput::make('gcash_number'),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
