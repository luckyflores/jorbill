<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('batch_id')
                    ->numeric(),
                TextInput::make('value_centavos')
                    ->numeric(),
                TextInput::make('duration_minutes')
                    ->numeric(),
                DateTimePicker::make('expires_at'),
                TextInput::make('status')
                    ->required()
                    ->default('unused'),
                TextInput::make('used_by_customer_id')
                    ->numeric(),
                TextInput::make('used_by_subscription_id')
                    ->numeric(),
                DateTimePicker::make('used_at'),
            ]);
    }
}
