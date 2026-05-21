<?php

namespace App\Filament\Resources\JobOrders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class JobOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('job_number')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('priority')
                    ->required()
                    ->default('normal'),
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('lead_id')
                    ->numeric(),
                TextInput::make('subscription_id')
                    ->numeric(),
                TextInput::make('assigned_to')
                    ->numeric(),
                DateTimePicker::make('scheduled_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                TextInput::make('location_lat')
                    ->numeric(),
                TextInput::make('location_lng')
                    ->numeric(),
                Textarea::make('address')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('resolution_notes')
                    ->columnSpanFull(),
            ]);
    }
}
