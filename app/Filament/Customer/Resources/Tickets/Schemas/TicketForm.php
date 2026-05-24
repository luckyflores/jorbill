<?php
namespace App\Filament\Customer\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')->required()->maxLength(255),
            Select::make('category')->required()
                ->options([
                    'billing'      => 'Billing question',
                    'connectivity' => 'Internet not working / slow',
                    'equipment'    => 'Router / ONU issue',
                    'other'        => 'Other',
                ])
                ->native(false)->default('connectivity'),
            Select::make('priority')->required()
                ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                ->default('normal')->native(false),
            Textarea::make('body')->label('What can we help with?')->required()->rows(5),
        ]);
    }
}
