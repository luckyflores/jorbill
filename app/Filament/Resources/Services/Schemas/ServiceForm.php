<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(255),
            Select::make('type')
                ->required()
                ->options([
                    'pppoe'   => 'PPPoE',
                    'hotspot' => 'Hotspot',
                    'ipoe'    => 'IPoE / DHCP',
                    'static'  => 'Static IP',
                ])
                ->native(false),
            TextInput::make('bandwidth_down_kbps')->numeric()->required()->suffix('kbps')->label('Download'),
            TextInput::make('bandwidth_up_kbps')->numeric()->required()->suffix('kbps')->label('Upload'),
            TextInput::make('price_centavos')
                ->numeric()
                ->required()
                ->prefix('? centavos')
                ->helperText('Price in centavos (e.g. 99900 = ?999.00)'),
            Toggle::make('vat_inclusive')->default(true),
            Select::make('billing_cycle')
                ->options(['monthly' => 'Monthly', 'prepaid_days' => 'Prepaid (days)'])
                ->default('monthly')
                ->native(false),
            TextInput::make('prepaid_days')->numeric()->nullable()->label('Prepaid days (if applicable)'),
            TextInput::make('mikrotik_profile_name')->maxLength(255)->label('Mikrotik profile name (for PPPoE)'),
            Textarea::make('description')->nullable()->columnSpanFull(),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}
