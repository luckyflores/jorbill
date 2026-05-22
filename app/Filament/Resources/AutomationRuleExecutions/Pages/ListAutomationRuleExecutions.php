<?php

namespace App\Filament\Resources\AutomationRuleExecutions\Pages;

use App\Filament\Resources\AutomationRuleExecutions\AutomationRuleExecutionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutomationRuleExecutions extends ListRecords
{
    protected static string $resource = AutomationRuleExecutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
