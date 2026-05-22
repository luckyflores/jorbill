<?php

namespace App\Filament\Resources\AutomationRules\Tables;

use App\Services\Automation\AutomationEngine;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutomationRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_enabled')->boolean()->label('On'),
                TextColumn::make('trigger_config.model')
                    ->label('Trigger model')
                    ->formatStateUsing(fn ($state) => class_basename($state ?? ''))
                    ->badge(),
                TextColumn::make('trigger_config.when')->label('When')->badge(),
                TextColumn::make('fire_count')->label('Fires')->numeric()->sortable(),
                TextColumn::make('last_fired_at')->dateTime()->sortable()->placeholder('never'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),                    Action::make('run_now')
                        ->label('Run now (force)')
                        ->icon('heroicon-o-bolt')
                        ->color('warning')
                        ->visible(fn ($record) => $record->trigger_type === 'scheduled')
                        ->requiresConfirmation()
                        ->modalDescription('Forces this scheduled rule to run immediately against all matching records (not dry-run — actions will execute).')
                        ->action(function ($record) {
                            $count = app(AutomationEngine::class)->fireScheduledRule($record, dryRun: false);
                            Notification::make()->title("Processed {$count} record(s)")->success()->send();
                        }),
                    Action::make('dry_run_now')
                        ->label('Dry-run now')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->visible(fn ($record) => $record->trigger_type === 'scheduled')
                        ->modalDescription('Runs this scheduled rule against current data in DRY-RUN — no SMS sent, no records changed. Records appear in the audit log as if matched.')
                        ->action(function ($record) {
                            $count = app(AutomationEngine::class)->fireScheduledRule($record, dryRun: true, limit: 20);
                            Notification::make()->title("Dry-ran against up to {$count} record(s)")->body('Check Automation → Rule Executions to see what would have happened.')->info()->persistent()->send();
                        }),

                    Action::make('test_fire')
                        ->label('Test fire')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->modalDescription('Simulates this rule against the most recent record of its trigger model. Actions run in DRY-RUN — no SMS sent, no records changed.')
                        ->action(function ($record) {
                            $modelClass = $record->trigger_config['model'] ?? null;
                            if (! $modelClass || ! class_exists($modelClass)) {
                                Notification::make()->title('Cannot test fire')->body('Trigger model not configured.')->danger()->send();
                                return;
                            }
                            $model = $modelClass::latest('id')->first();
                            if (! $model) {
                                Notification::make()->title('No record to test against')->body("No {$modelClass} records exist yet.")->warning()->send();
                                return;
                            }
                            $exec = app(AutomationEngine::class)->fireRule($record, $model, dryRun: true);

                            $lines = [];
                            $lines[] = "Tested against: " . class_basename($modelClass) . " #{$model->getKey()}";
                            $lines[] = "Conditions matched: " . ($exec->conditions_matched ? '✓ YES' : '✗ NO');
                            if ($exec->conditions_matched) {
                                foreach (($exec->actions_executed ?? []) as $r) {
                                    $lines[] = "  " . ($r['ok'] ? '✓' : '✗') . " {$r['type']}: " . ($r['detail'] ?? '');
                                }
                            }
                            Notification::make()->title('Test fire complete')
                                ->body(implode("\n", $lines))
                                ->{$exec->conditions_matched ? 'success' : 'warning'}()
                                ->persistent()
                                ->send();
                        }),
                    DeleteAction::make(),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
