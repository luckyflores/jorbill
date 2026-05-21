<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('customer_id')->numeric()->required(),
            TextInput::make('service_id')->numeric()->required(),
            TextInput::make('router_id')->numeric()->nullable(),
            Select::make('status')
                ->required()
                ->options([
                    'pending'   => 'Pending',
                    'active'    => 'Active',
                    'suspended' => 'Suspended',
                    'cancelled' => 'Cancelled',
                ])
                ->default('pending')
                ->native(false),
            TextInput::make('username')->nullable()->label('PPPoE / Hotspot username'),
            TextInput::make('password')->password()->revealable()->nullable()->label('PPPoE / Hotspot password (encrypted)'),
            TextInput::make('mac_address')->nullable()->label('MAC address (IPoE)'),
            TextInput::make('ip_address')->nullable()->label('IP address (Framed-IP for PPP / target for IPoE)'),
            TextInput::make('price_centavos_override')->numeric()->nullable()->prefix('? centavos'),
            DateTimePicker::make('activated_at')->nullable(),
            DateTimePicker::make('suspended_at')->nullable(),
            DateTimePicker::make('cancelled_at')->nullable(),
            DatePicker::make('next_billing_date')->nullable(),
            Textarea::make('notes')->nullable()->columnSpanFull(),
        ]);
    }
}
