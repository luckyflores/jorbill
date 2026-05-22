<?php

namespace App\Filament\Resources\AutomationRules;

use App\Filament\Resources\AutomationRules\Pages\CreateAutomationRule;
use App\Filament\Resources\AutomationRules\Pages\EditAutomationRule;
use App\Filament\Resources\AutomationRules\Pages\ListAutomationRules;
use App\Filament\Resources\AutomationRules\Schemas\AutomationRuleForm;
use App\Filament\Resources\AutomationRules\Tables\AutomationRulesTable;
use App\Models\AutomationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutomationRuleResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Automation';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bolt-slash';

    protected static ?string $model = AutomationRule::class;
    public static function form(Schema $schema): Schema
    {
        return AutomationRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutomationRulesTable::configure($table);
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
            'index' => ListAutomationRules::route('/'),
            'create' => CreateAutomationRule::route('/create'),
            'edit' => EditAutomationRule::route('/{record}/edit'),
        ];
    }
}
