<?php

namespace App\Filament\Resources\CustomerDiagnostics\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerDiagnosticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('tech_user_id')
                    ->numeric(),
                DateTimePicker::make('ran_at')
                    ->required(),
                TextInput::make('public_ip'),
                TextInput::make('wifi'),
                TextInput::make('ping_results'),
                TextInput::make('speedtest'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('gps_lat')
                    ->numeric(),
                TextInput::make('gps_lng')
                    ->numeric(),
                TextInput::make('photo_path'),
                TextInput::make('app_version'),
                TextInput::make('device_info'),
            ]);
    }
}
