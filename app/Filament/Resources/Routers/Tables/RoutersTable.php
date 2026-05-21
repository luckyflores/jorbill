<?php

namespace App\Filament\Resources\Routers\Tables;

use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Services\Network\Contracts\MikrotikClientFactory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoutersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('vendor')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('api_port')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('api_user')
                    ->searchable(),
                TextColumn::make('ssh_port')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
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
                                Action::make('test_connection')
                    ->label('Test connection')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function ($record) {
                        $client = app(MikrotikClientFactory::class)->forRouter($record);
                        $ok = $client->connect();
                        $client->disconnect();
                        $record->update(['last_seen_at' => $ok ? now() : $record->last_seen_at]);
                        Notification::make()
                            ->title($ok ? 'Connected ?' : 'Connection failed')
                            ->body($ok ? "Reached {$record->ip_address}:{$record->api_port}" : "Could not reach {$record->ip_address}:{$record->api_port} ? check IP, port, creds, and that API service is enabled on the router.")
                            ->{$ok ? 'success' : 'danger'}()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
