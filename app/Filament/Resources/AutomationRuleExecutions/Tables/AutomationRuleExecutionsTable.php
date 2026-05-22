<?php

namespace App\Filament\Resources\AutomationRuleExecutions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutomationRuleExecutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fired_at', 'desc')
            ->columns([
                TextColumn::make('rule.name')->label('Rule')->searchable()->sortable(),
                TextColumn::make('fired_at')->dateTime('M d H:i:s')->sortable(),
                TextColumn::make('trigger_summary')->label('Trigger'),
                IconColumn::make('conditions_matched')->boolean()->label('Matched'),
                TextColumn::make('actions_executed')
                    ->label('Actions')
                    ->formatStateUsing(function ($state) {
                        if (! is_array($state)) return '—';
                        $oks = count(array_filter($state, fn($a) => ($a['ok'] ?? false)));
                        return "{$oks}/" . count($state) . " ok";
                    }),
                TextColumn::make('duration_ms')->label('ms')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
