<?php

namespace App\Filament\Resources\AutomationRuleExecutions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AutomationRuleExecutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rule_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('fired_at')
                    ->required(),
                TextInput::make('trigger_summary'),
                TextInput::make('trigger_payload'),
                Toggle::make('conditions_matched')
                    ->required(),
                TextInput::make('actions_executed'),
                TextInput::make('duration_ms')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('error')
                    ->columnSpanFull(),
            ]);
    }
}
