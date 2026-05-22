<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Notifications\Notification;

use Filament\Actions\Action;

use App\Models\InvoiceLineItem;

use App\Models\Customer;

use App\Services\Odoo\Contracts\OdooClient;

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
                                Action::make('push_invoice_to_odoo')
                    ->label('Push to Odoo')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->odoo_id)
                    ->requiresConfirmation()
                    ->modalDescription('Creates the matching account.move (customer invoice) in Odoo + posts it. Pushes the customer to Odoo first if not already pushed. Idempotent — if odoo_id is already set, this action is hidden.')
                    ->action(function ($record) {
                        $odoo = app(OdooClient::class);

                        // 1. Resolve customer partner (push customer first if needed)
                        $customer = Customer::find($record->customer_id);
                        if (! $customer) {
                            Notification::make()->title('Push failed: customer missing')->danger()->send();
                            return;
                        }
                        $partnerId = $customer->odoo_id;
                        if (! $partnerId) {
                            $partnerId = $odoo->findOrCreatePartner($customer->toArray());
                            if (! $partnerId) {
                                Notification::make()->title('Could not create Odoo partner')->danger()->send();
                                return;
                            }
                            $customer->forceFill(['odoo_id' => $partnerId, 'odoo_synced_at' => now()])->save();
                        }

                        // 2. Gather line items
                        $lines = InvoiceLineItem::where('invoice_id', $record->id)->get()->map(fn ($l) => [
                            'description'         => $l->description,
                            'quantity'            => $l->quantity,
                            'unit_price_centavos' => $l->unit_price_centavos,
                        ])->toArray();

                        if (empty($lines)) {
                            $lines = [[
                                'description' => 'Service - invoice ' . $record->invoice_number,
                                'quantity' => 1,
                                'unit_price_centavos' => $record->total_centavos,
                            ]];
                        }

                        // 3. Push invoice
                        $odooId = $odoo->pushInvoice($record->toArray(), $lines, $partnerId);
                        if ($odooId !== null) {
                            $record->forceFill(['odoo_id' => $odooId, 'odoo_synced_at' => now()])->save();
                            Notification::make()->title('Pushed to Odoo')->body('Odoo invoice id: ' . $odooId)->success()->send();
                        } else {
                            Notification::make()->title('Push failed')->body('Check Odoo logs.')->danger()->send();
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
