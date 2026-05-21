<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ticket_number')
                    ->required(),
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                TextInput::make('priority')
                    ->required()
                    ->default('normal'),
                TextInput::make('category')
                    ->required()
                    ->default('other'),
                TextInput::make('channel')
                    ->required()
                    ->default('portal'),
                TextInput::make('assigned_to')
                    ->numeric(),
                TextInput::make('subscription_id')
                    ->numeric(),
                DateTimePicker::make('resolved_at'),
                DateTimePicker::make('first_response_at'),
            ]);
    }
}
