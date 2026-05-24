<?php

namespace App\Filament\Customer\Resources\Subscriptions;

use App\Filament\Customer\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Customer\Resources\Subscriptions\Tables\SubscriptionsTable;
use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationLabel = 'My Subscriptions';
    protected static ?string $modelLabel = 'Subscription';
    protected static ?int $navigationSort = 20;

    public static function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('customer_id', auth('customer')->id());
    }

    public static function getPages(): array
    {
        return ['index' => ListSubscriptions::route('/')];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}
