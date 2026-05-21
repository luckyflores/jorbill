<?php

namespace App\Filament\Resources\Naps;

use App\Filament\Resources\Naps\Pages\CreateNap;
use App\Filament\Resources\Naps\Pages\EditNap;
use App\Filament\Resources\Naps\Pages\ListNaps;
use App\Filament\Resources\Naps\Schemas\NapForm;
use App\Filament\Resources\Naps\Tables\NapsTable;
use App\Models\Nap;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NapResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Network';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $model = Nap::class;
    public static function form(Schema $schema): Schema
    {
        return NapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NapsTable::configure($table);
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
            'index' => ListNaps::route('/'),
            'create' => CreateNap::route('/create'),
            'edit' => EditNap::route('/{record}/edit'),
        ];
    }
}
