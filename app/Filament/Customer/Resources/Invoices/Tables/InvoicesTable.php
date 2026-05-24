<?php

namespace App\Filament\Customer\Resources\Invoices\Tables;

use App\Services\Payment\Contracts\PaymentGateway;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('invoice_number')->label('Invoice #')->searchable()->sortable(),
                TextColumn::make('issued_at')->date()->label('Issued'),
                TextColumn::make('due_at')->date()->label('Due'),
                TextColumn::make('total_centavos')->label('Total')
                    ->money('PHP', divideBy: 100)
                    ->sortable(),
                TextColumn::make('amount_paid_centavos')->label('Paid')
                    ->money('PHP', divideBy: 100),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'issued'  => 'warning',
                        'overdue' => 'danger',
                        default   => 'gray',
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('pay')
                    ->label('Pay now')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['issued', 'overdue'], true))
                    ->action(function ($record) {
                        $gateway = app(PaymentGateway::class);
                        if ($gateway->id() !== 'hitpay') {
                            Notification::make()
                                ->title('Online payment not configured')
                                ->body('Please contact us to settle this invoice.')
                                ->warning()->send();
                            return null;
                        }
                        $customer = auth('customer')->user();
                        $amountDue = $record->total_centavos - ($record->amount_paid_centavos ?? 0);
                        try {
                            $r = $gateway->createCheckout(
                                amountCentavos: $amountDue,
                                invoiceNumber: $record->invoice_number,
                                customer: [
                                    'name'  => $customer?->name,
                                    'email' => $customer?->email,
                                    'phone' => $customer?->phone,
                                ],
                                callbackUrl: url('/portal'),
                            );
                            return redirect()->away($r['checkout_url']);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Could not start checkout')
                                ->body($e->getMessage())
                                ->danger()->send();
                            return null;
                        }
                    }),
            ]);
    }
}
