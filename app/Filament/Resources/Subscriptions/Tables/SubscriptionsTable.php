<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Services\Subscriptions\SubscriptionProvisioner;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('customer_id')->sortable()->label('Customer'),
                TextColumn::make('service_id')->sortable()->label('Service'),
                TextColumn::make('router_id')->sortable()->label('Router'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'suspended' => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('username')->searchable(),
                TextColumn::make('ip_address')->searchable(),
                TextColumn::make('next_billing_date')->date()->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('sync')
                        ->label('Sync to router')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function ($record) {
                            $ok = app(SubscriptionProvisioner::class)->sync($record);
                            Notification::make()
                                ->title($ok ? 'Synced to router' : 'Sync failed (see logs)')
                                ->{$ok ? 'success' : 'danger'}()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Action::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn ($record) => $record->status !== 'active')
                        ->action(function ($record) {
                            $record->update(['status' => 'active', 'activated_at' => $record->activated_at ?? now()]);
                            $ok = app(SubscriptionProvisioner::class)->sync($record);
                            Notification::make()
                                ->title($ok ? 'Activated + synced' : 'Activated but sync failed')
                                ->{$ok ? 'success' : 'warning'}()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Action::make('suspend')
                        ->label('Suspend')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->visible(fn ($record) => $record->status === 'active')
                        ->action(function ($record) {
                            $record->update(['status' => 'suspended', 'suspended_at' => now()]);
                            $ok = app(SubscriptionProvisioner::class)->sync($record);
                            Notification::make()
                                ->title($ok ? 'Suspended + synced' : 'Suspended but sync failed')
                                ->{$ok ? 'success' : 'warning'}()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status !== 'cancelled')
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                            $ok = app(SubscriptionProvisioner::class)->sync($record);
                            Notification::make()
                                ->title($ok ? 'Cancelled + synced' : 'Cancelled but sync failed')
                                ->{$ok ? 'success' : 'warning'}()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    DeleteAction::make(),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
