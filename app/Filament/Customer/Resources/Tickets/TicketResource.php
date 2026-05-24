<?php

namespace App\Filament\Customer\Resources\Tickets;

use App\Filament\Customer\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Customer\Resources\Tickets\Pages\ListTickets;
use App\Filament\Customer\Resources\Tickets\Pages\ViewTicket;
use App\Filament\Customer\Resources\Tickets\Schemas\TicketForm;
use App\Filament\Customer\Resources\Tickets\Tables\TicketsTable;
use App\Models\Ticket;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationLabel = 'Support Tickets';
    protected static ?string $modelLabel = 'Ticket';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema { return TicketForm::configure($schema); }
    public static function table(Table $table): Table { return TicketsTable::configure($table); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('customer_id', auth('customer')->id());
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view'   => ViewTicket::route('/{record}'),
        ];
    }

    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}
