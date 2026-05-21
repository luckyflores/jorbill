<?php

namespace App\Filament\Resources\Onus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OnusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->searchable(),
                TextColumn::make('vendor')
                    ->searchable(),
                TextColumn::make('model_name')
                    ->searchable(),
                TextColumn::make('mac_address')
                    ->searchable(),
                TextColumn::make('subscription_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nap_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nap_port')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rx_power_dbm')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tx_power_dbm')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('installed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
