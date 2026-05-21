<?php

namespace App\Filament\Resources\NotificationLogs;

use App\Filament\Resources\NotificationLogs\Pages\CreateNotificationLog;
use App\Filament\Resources\NotificationLogs\Pages\EditNotificationLog;
use App\Filament\Resources\NotificationLogs\Pages\ListNotificationLogs;
use App\Filament\Resources\NotificationLogs\Schemas\NotificationLogForm;
use App\Filament\Resources\NotificationLogs\Tables\NotificationLogsTable;
use App\Models\NotificationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $model = NotificationLog::class;
    public static function form(Schema $schema): Schema
    {
        return NotificationLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationLogsTable::configure($table);
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
            'index' => ListNotificationLogs::route('/'),
            'create' => CreateNotificationLog::route('/create'),
            'edit' => EditNotificationLog::route('/{record}/edit'),
        ];
    }
}
