<?php

namespace App\Filament\Resources\AutomationRuleExecutions;

use App\Filament\Resources\AutomationRuleExecutions\Pages\CreateAutomationRuleExecution;
use App\Filament\Resources\AutomationRuleExecutions\Pages\EditAutomationRuleExecution;
use App\Filament\Resources\AutomationRuleExecutions\Pages\ListAutomationRuleExecutions;
use App\Filament\Resources\AutomationRuleExecutions\Schemas\AutomationRuleExecutionForm;
use App\Filament\Resources\AutomationRuleExecutions\Tables\AutomationRuleExecutionsTable;
use App\Models\AutomationRuleExecution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutomationRuleExecutionResource extends Resource
{
    public static function canCreate(): bool { return false; }

    protected static \UnitEnum|string|null $navigationGroup = 'Automation';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $model = AutomationRuleExecution::class;
    public static function form(Schema $schema): Schema
    {
        return AutomationRuleExecutionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutomationRuleExecutionsTable::configure($table);
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
            'index' => ListAutomationRuleExecutions::route('/'),
            'edit' => EditAutomationRuleExecution::route('/{record}/edit'),
        ];
    }
}
