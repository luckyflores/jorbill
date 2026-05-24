<?php
namespace App\Filament\Customer\Resources\Tickets\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('ticket_number')->label('Ticket')->fontFamily('mono'),
                TextColumn::make('subject')->limit(50)->searchable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'open'     => 'warning',
                        'pending'  => 'info',
                        'resolved' => 'success',
                        'closed'   => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->label('Opened'),
            ])
            ->recordActions([ViewAction::make()]);
    }
}
