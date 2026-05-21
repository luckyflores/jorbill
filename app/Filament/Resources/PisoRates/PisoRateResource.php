<?php

namespace App\Filament\Resources\PisoRates;

use App\Filament\Resources\PisoRates\Pages\CreatePisoRate;
use App\Filament\Resources\PisoRates\Pages\EditPisoRate;
use App\Filament\Resources\PisoRates\Pages\ListPisoRates;
use App\Filament\Resources\PisoRates\Schemas\PisoRateForm;
use App\Filament\Resources\PisoRates\Tables\PisoRatesTable;
use App\Models\PisoRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PisoRateResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Hotspot / PAYG';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $model = PisoRate::class;
    public static function form(Schema $schema): Schema
    {
        return PisoRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PisoRatesTable::configure($table);
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
            'index' => ListPisoRates::route('/'),
            'create' => CreatePisoRate::route('/create'),
            'edit' => EditPisoRate::route('/{record}/edit'),
        ];
    }
}
