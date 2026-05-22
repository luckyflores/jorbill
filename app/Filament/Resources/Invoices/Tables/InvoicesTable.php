<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Services\Payment\Contracts\PaymentGateway;

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
                                Action::make('send_payment_link')
                    ->label('Send payment link')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['issued', 'overdue'], true))
                    ->requiresConfirmation()
                    ->modalDescription('Creates a HitPay payment-request and shows you the checkout URL. The customer pays on HitPay; their webhook calls back here and the Payment row is created automatically.')
                    ->action(function ($record) {
                        $gateway = app(PaymentGateway::class);
                        if ($gateway->id() !== 'hitpay') {
                            \Filament\Notifications\Notification::make()
                                ->title('PAYMENT_GATEWAY env is not "hitpay"')
                                ->body('Set PAYMENT_GATEWAY=hitpay + HITPAY_API_KEY + HITPAY_SALT in .env, then restart the app.')
                                ->warning()->send();
                            return;
                        }

                        $customer = \App\Models\Customer::find($record->customer_id);
                        $amountDue = $record->total_centavos - ($record->amount_paid_centavos ?? 0);
                        if ($amountDue <= 0) {
                            \Filament\Notifications\Notification::make()->title('Invoice already paid')->warning()->send();
                            return;
                        }

                        try {
                            $result = $gateway->createCheckout(
                                amountCentavos: $amountDue,
                                invoiceNumber: $record->invoice_number,
                                customer: [
                                    'name'  => $customer?->name,
                                    'email' => $customer?->email,
                                    'phone' => $customer?->phone,
                                ],
                                callbackUrl: (string) config('payment.gateways.hitpay.redirect_url'),
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('Payment link ready')
                                ->body('Send this URL to the customer: ' . $result['checkout_url'])
                                ->success()
                                ->persistent()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Could not create payment link')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
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
