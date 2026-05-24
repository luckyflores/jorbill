<?php

namespace App\Filament\Resources\CustomerDiagnostics;

use App\Filament\Resources\CustomerDiagnostics\Pages\CreateCustomerDiagnostic;
use App\Filament\Resources\CustomerDiagnostics\Pages\EditCustomerDiagnostic;
use App\Filament\Resources\CustomerDiagnostics\Pages\ListCustomerDiagnostics;
use App\Filament\Resources\CustomerDiagnostics\Pages\ViewCustomerDiagnostic;
use App\Filament\Resources\CustomerDiagnostics\Schemas\CustomerDiagnosticForm;
use App\Filament\Resources\CustomerDiagnostics\Schemas\CustomerDiagnosticInfolist;
use App\Filament\Resources\CustomerDiagnostics\Tables\CustomerDiagnosticsTable;
use App\Models\CustomerDiagnostic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerDiagnosticResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Operations';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-signal';

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    protected static ?string $model = CustomerDiagnostic::class;
    public static function form(Schema $schema): Schema
    {
        return CustomerDiagnosticForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerDiagnosticInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerDiagnosticsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerDiagnostics::route('/'),
            'create' => CreateCustomerDiagnostic::route('/create'),
            'view' => ViewCustomerDiagnostic::route('/{record}'),
            'edit' => EditCustomerDiagnostic::route('/{record}/edit'),
        ];
    }
}
