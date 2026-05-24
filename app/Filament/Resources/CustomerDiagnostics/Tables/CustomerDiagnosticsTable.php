<?php

namespace App\Filament\Resources\CustomerDiagnostics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerDiagnosticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tech_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ran_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('public_ip')
                    ->searchable(),
                TextColumn::make('gps_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gps_lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('photo_path')
                    ->searchable(),
                TextColumn::make('app_version')
                    ->searchable(),
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
