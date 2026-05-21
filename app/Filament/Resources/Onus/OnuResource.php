<?php

namespace App\Filament\Resources\Onus;

use App\Filament\Resources\Onus\Pages\CreateOnu;
use App\Filament\Resources\Onus\Pages\EditOnu;
use App\Filament\Resources\Onus\Pages\ListOnus;
use App\Filament\Resources\Onus\Schemas\OnuForm;
use App\Filament\Resources\Onus\Tables\OnusTable;
use App\Models\Onu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OnuResource extends Resource
{
    protected static ?string $model = Onu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OnuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnusTable::configure($table);
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
            'index' => ListOnus::route('/'),
            'create' => CreateOnu::route('/create'),
            'edit' => EditOnu::route('/{record}/edit'),
        ];
    }
}
