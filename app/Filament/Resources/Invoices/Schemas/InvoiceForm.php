<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required(),
                TextInput::make('series_code')
                    ->required()
                    ->default('SI'),
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('subscription_id')
                    ->numeric(),
                DatePicker::make('issued_at')
                    ->required(),
                DatePicker::make('due_at')
                    ->required(),
                TextInput::make('subtotal_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('vat_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('withholding_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('amount_paid_centavos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('bir_atp_id')
                    ->numeric(),
            ]);
    }
}
