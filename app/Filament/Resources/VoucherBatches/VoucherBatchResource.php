<?php

namespace App\Filament\Resources\VoucherBatches;

use App\Filament\Resources\VoucherBatches\Pages\CreateVoucherBatch;
use App\Filament\Resources\VoucherBatches\Pages\EditVoucherBatch;
use App\Filament\Resources\VoucherBatches\Pages\ListVoucherBatches;
use App\Filament\Resources\VoucherBatches\Schemas\VoucherBatchForm;
use App\Filament\Resources\VoucherBatches\Tables\VoucherBatchesTable;
use App\Models\VoucherBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VoucherBatchResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Hotspot / PAYG';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $model = VoucherBatch::class;
    public static function form(Schema $schema): Schema
    {
        return VoucherBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VoucherBatchesTable::configure($table);
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
            'index' => ListVoucherBatches::route('/'),
            'create' => CreateVoucherBatch::route('/create'),
            'edit' => EditVoucherBatch::route('/{record}/edit'),
        ];
    }
}
