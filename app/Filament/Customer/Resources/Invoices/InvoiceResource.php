<?php

namespace App\Filament\Customer\Resources\Invoices;

use App\Filament\Customer\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Customer\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Customer\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationLabel = 'My Invoices';
    protected static ?string $modelLabel = 'Invoice';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('customer_id', auth('customer')->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'view'  => ViewInvoice::route('/{record}'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}
