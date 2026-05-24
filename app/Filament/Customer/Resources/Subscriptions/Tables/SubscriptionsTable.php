<?php

namespace App\Filament\Customer\Resources\Subscriptions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')->label('Username')->fontFamily('mono'),
                TextColumn::make('service.name')->label('Plan'),
                TextColumn::make('service.bandwidth_down_kbps')
                    ->label('Speed')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->service?->bandwidth_down_kbps ?? 0) / 1000 . ' / ' .
                        ($record->service?->bandwidth_up_kbps ?? 0) / 1000 . ' Mbps'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'suspended' => 'danger',
                        'pending'   => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('activated_at')->date()->label('Active since'),
                TextColumn::make('next_billing_date')->date()->label('Next billing'),
            ]);
    }
}
