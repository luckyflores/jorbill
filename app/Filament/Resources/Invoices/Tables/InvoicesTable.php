<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('series_code')
                    ->searchable(),
                TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subscription_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('issued_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('subtotal_centavos')
                    ->label('Subtotal')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vat_centavos')
                    ->label('VAT')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('withholding_centavos')
                    ->label('Withholding')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_centavos')
                    ->label('Discount')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_centavos')
                    ->label('Total')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount_paid_centavos')
                    ->label('Paid')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('bir_atp_id')
                    ->numeric()
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
