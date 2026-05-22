<?php

namespace App\Filament\Resources\AutomationRuleExecutions\Pages;

use App\Filament\Resources\AutomationRuleExecutions\AutomationRuleExecutionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAutomationRuleExecution extends EditRecord
{
    protected static string $resource = AutomationRuleExecutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
