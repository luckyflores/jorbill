<?php

namespace App\Filament\Resources\NotificationTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identification')
                ->columns(2)
                ->components([
                    TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(64)
                        ->helperText('Lowercase slug used by automation rules (e.g. payment_received). Cannot be changed without breaking rules that reference it.')
                        ->columnSpan(1),
                    TextInput::make('label')->maxLength(255)->columnSpan(1),
                    Textarea::make('description')->columnSpanFull(),
                    Toggle::make('is_active')->default(true),
                ]),

            Section::make('Content')
                ->components([
                    Select::make('channel')
                        ->required()
                        ->options([
                            'sms'      => 'SMS',
                            'email'    => 'Email',
                            'whatsapp' => 'WhatsApp',
                        ])
                        ->default('sms')
                        ->native(false),
                    TextInput::make('subject')
                        ->visible(fn ($get) => $get('channel') === 'email')
                        ->maxLength(255),
                    Textarea::make('body')
                        ->required()
                        ->rows(6)
                        ->helperText('Use {{customer.name}}, {{customer.phone}}, {{subscription.username}}, {{invoice.invoice_number}}, {{invoice.total_centavos}}, {{invoice.days_overdue}}, {{invoice.days_until_due}}, {{payment.amount_centavos}}, etc.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
