<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Notifications\Notification;

use Filament\Actions\Action;

use App\Services\Odoo\Contracts\OdooClient;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('alt_phone')
                    ->searchable(),
                TextColumn::make('address_line1')
                    ->searchable(),
                TextColumn::make('barangay')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('province')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('tax_id')
                    ->searchable(),
                TextColumn::make('agent_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('activated_at')
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                                Action::make('push_to_odoo')
                    ->label('Push to Odoo')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Creates or updates the matching res.partner record in Odoo, using customer_code as the cross-reference. Idempotent.')
                    ->action(function ($record) {
                        $odoo = app(OdooClient::class);
                        $id = $odoo->findOrCreatePartner($record->toArray());
                        if ($id !== null) {
                            Notification::make()
                                ->title('Pushed to Odoo')
                                ->body('Odoo partner id: ' . $id)
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('Push failed')
                                ->body('Check Odoo connection settings + logs.')
                                ->danger()->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
