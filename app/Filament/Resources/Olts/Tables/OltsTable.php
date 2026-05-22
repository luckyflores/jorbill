<?php

namespace App\Filament\Resources\Olts\Tables;

use App\Services\Network\Contracts\OltClientFactory;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OltsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('vendor')->badge()->sortable(),
                TextColumn::make('model'),
                TextColumn::make('ip_address')->copyable()->fontFamily('mono'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_seen_at')->dateTime()->sortable()->placeholder('—'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('test_connection')
                        ->label('Test connection')
                        ->icon('heroicon-o-signal')
                        ->color('info')
                        ->action(function ($record) {
                            $client = app(OltClientFactory::class)->forOlt($record);
                            $ok = $client->connect();
                            $client->disconnect();
                            if ($ok) {
                                $record->update(['last_seen_at' => now()]);
                            }
                            Notification::make()
                                ->title($ok ? 'Connected ✓' : 'Connection failed')
                                ->body($ok ? "Reached {$record->ip_address}:{$record->ssh_port}" : 'Check IP, port, creds, SSH access.')
                                ->{$ok ? 'success' : 'danger'}()
                                ->send();
                        }),

                    Action::make('list_pon_ports')
                        ->label('List PON ports')
                        ->icon('heroicon-o-rectangle-stack')
                        ->action(function ($record) {
                            $client = app(OltClientFactory::class)->forOlt($record);
                            if (! $client->connect()) {
                                Notification::make()->title('Could not connect')->danger()->send();
                                return;
                            }
                            $ports = $client->listPonPorts();
                            $client->disconnect();
                            Notification::make()
                                ->title(count($ports) . ' PON port(s) found')
                                ->body(implode("\n", array_map(fn($p) => $p['identifier'] . ' [' . $p['status'] . ']', $ports)) ?: 'None')
                                ->success()->send();
                        }),

                    Action::make('list_uncfg_onus')
                        ->label('List unprovisioned ONUs')
                        ->icon('heroicon-o-question-mark-circle')
                        ->color('warning')
                        ->action(function ($record) {
                            $client = app(OltClientFactory::class)->forOlt($record);
                            if (! $client->connect()) {
                                Notification::make()->title('Could not connect')->danger()->send();
                                return;
                            }
                            $onus = $client->listUnconfiguredOnus();
                            $client->disconnect();
                            Notification::make()
                                ->title(count($onus) . ' unprovisioned ONU(s)')
                                ->body(implode("\n", array_map(fn($o) => $o['serial_number'] . ' on ' . $o['pon'], $onus)) ?: 'None — every ONU is already configured.')
                                ->{count($onus) > 0 ? 'warning' : 'success'}()
                                ->send();
                        }),

                    Action::make('raw_command')
                        ->label('Raw CLI command')
                        ->icon('heroicon-o-command-line')
                        ->color('gray')
                        ->schema([
                            TextInput::make('command')
                                ->required()
                                ->placeholder('show version')
                                ->helperText('Sent verbatim after entering privileged mode. Use this for anything our structured methods do not cover.'),
                        ])
                        ->action(function ($record, array $data) {
                            $client = app(OltClientFactory::class)->forOlt($record);
                            if (! $client->connect()) {
                                Notification::make()->title('Could not connect')->danger()->send();
                                return;
                            }
                            $output = $client->rawCommand($data['command']);
                            $client->disconnect();
                            Notification::make()
                                ->title('Output')
                                ->body(substr($output ?? '(no output)', 0, 2000))
                                ->info()->persistent()->send();
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
