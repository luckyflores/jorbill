<?php

namespace App\Filament\Resources\Routers;

use App\Filament\Resources\Routers\Pages\CreateRouter;
use App\Filament\Resources\Routers\Pages\EditRouter;
use App\Filament\Resources\Routers\Pages\ListRouters;
use App\Filament\Resources\Routers\Schemas\RouterForm;
use App\Filament\Resources\Routers\Tables\RoutersTable;
use App\Models\Router;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RouterResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RouterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoutersTable::configure($table);
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
            'index' => ListRouters::route('/'),
            'create' => CreateRouter::route('/create'),
            'edit' => EditRouter::route('/{record}/edit'),
        ];
    }
}
