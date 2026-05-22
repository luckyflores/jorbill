<?php

namespace App\Filament\Resources\Olts;

use App\Filament\Resources\Olts\Pages\CreateOlt;
use App\Filament\Resources\Olts\Pages\EditOlt;
use App\Filament\Resources\Olts\Pages\ListOlts;
use App\Filament\Resources\Olts\Schemas\OltForm;
use App\Filament\Resources\Olts\Tables\OltsTable;
use App\Models\Olt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OltResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Network';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $model = Olt::class;
    public static function form(Schema $schema): Schema
    {
        return OltForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OltsTable::configure($table);
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
            'index' => ListOlts::route('/'),
            'create' => CreateOlt::route('/create'),
            'edit' => EditOlt::route('/{record}/edit'),
        ];
    }
}
