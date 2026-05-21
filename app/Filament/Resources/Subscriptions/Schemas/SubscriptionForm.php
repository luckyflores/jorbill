<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('service_id')
                    ->required()
                    ->numeric(),
                TextInput::make('router_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('username'),
                TextInput::make('password')
                    ->password(),
                TextInput::make('mac_address'),
                TextInput::make('ip_address'),
                TextInput::make('price_centavos_override')
                    ->numeric(),
                DateTimePicker::make('activated_at'),
                DateTimePicker::make('suspended_at'),
                DateTimePicker::make('cancelled_at'),
                DatePicker::make('next_billing_date'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
