<?php

namespace App\Filament\Resources\NotificationLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NotificationLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('channel')
                    ->required(),
                TextInput::make('driver')
                    ->required(),
                TextInput::make('to')
                    ->required(),
                TextInput::make('subject'),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('event'),
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('queued'),
                TextInput::make('gateway_reference'),
                Textarea::make('error')
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at'),
            ]);
    }
}
