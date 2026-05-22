<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Customer;

use App\Services\Odoo\Contracts\OdooClient;

use Filament\Notifications\Notification;

use Filament\Forms\Components\Textarea;

use Filament\Forms\Components\Select;

use Filament\Actions\Action;

use App\Services\Billing\PaymentReversalService;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->searchable(),
                TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invoice_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount_centavos')
                    ->label('Amount')
                    ->money('PHP', divideBy: 100)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gateway')
                    ->searchable(),
                TextColumn::make('gateway_reference')
                    ->searchable(),
                TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
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
            ->recordActions([                Action::make('push_payment_to_odoo')
                    ->label('Push to Odoo')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->odoo_id && in_array($record->status, ['completed', 'reversal'], true))
                    ->requiresConfirmation()
                    ->modalDescription('Creates the matching account.payment in Odoo + posts it. Pushes the customer first if needed. Idempotent.')
                    ->action(function ($record) {
                        $odoo = app(OdooClient::class);

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

                        $odooId = $odoo->pushPayment($record->toArray(), $partnerId);
                        if ($odooId !== null) {
                            $record->forceFill(['odoo_id' => $odooId, 'odoo_synced_at' => now()])->save();
                            Notification::make()->title('Pushed to Odoo')->body('Odoo payment id: ' . $odooId)->success()->send();
                        } else {
                            Notification::make()->title('Push failed')->body('Check Odoo logs.')->danger()->send();
                        }
                    }),
                
                                Action::make('reverse_payment')
                    ->label('Reverse payment')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['completed', 'pending'], true))
                    ->requiresConfirmation()
                    ->modalDescription('Creates an offsetting reversal entry. The original payment stays in the audit trail; the linked invoice will recompute its paid status. This action cannot be undone — there is no "un-reverse".')
                    ->schema([
                        Select::make('reason')
                            ->required()
                            ->options([
                                'bounced_check'      => 'Bounced check / NSF',
                                'refund'             => 'Refund to customer',
                                'duplicate'          => 'Duplicate payment',
                                'wrong_amount'       => 'Wrong amount entered',
                                'wrong_customer'     => 'Applied to wrong customer',
                                'gateway_chargeback' => 'Gateway chargeback',
                                'other'              => 'Other (use notes)',
                            ])
                            ->native(false),
                        Textarea::make('notes')
                            ->placeholder('Free-form context — preserved in audit log'),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $reversal = app(PaymentReversalService::class)->reverse(
                                $record,
                                $data['reason'],
                                $data['notes'] ?? null,
                            );
                            Notification::make()
                                ->title('Payment reversed')
                                ->body("Reversal {$reversal->payment_number} created. Invoice recomputed.")
                                ->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Reverse failed')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
