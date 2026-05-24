<?php

namespace App\Filament\Resources\CustomerDiagnostics\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerDiagnosticInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('tech_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ran_at')
                    ->dateTime(),
                TextEntry::make('public_ip')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('gps_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('gps_lng')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('photo_path')
                    ->placeholder('-'),
                TextEntry::make('app_version')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
