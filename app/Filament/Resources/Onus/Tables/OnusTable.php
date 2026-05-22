<?php

namespace App\Filament\Resources\Onus\Tables;

use App\Services\Network\Contracts\GenieAcsClient;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OnusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->searchable(),
                TextColumn::make('vendor')
                    ->searchable(),
                TextColumn::make('model_name')
                    ->searchable(),
                TextColumn::make('mac_address')
                    ->searchable(),
                TextColumn::make('subscription_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nap_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nap_port')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rx_power_dbm')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tx_power_dbm')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('installed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('tr069_reboot')
                        ->label('Reboot (TR-069)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $acs = app(GenieAcsClient::class);
                            $device = $acs->findDeviceBySerial($record->serial_number);
                            if (! $device) {
                                Notification::make()->title('Device not found in ACS')->body('Serial: ' . $record->serial_number)->warning()->send();
                                return;
                            }
                            $ok = $acs->reboot($device['_id']);
                            Notification::make()->title($ok ? 'Reboot scheduled' : 'Reboot failed')->{$ok ? 'success' : 'danger'}()->send();
                        }),
                    Action::make('tr069_set_wifi')
                        ->label('Set Wi-Fi (TR-069)')
                        ->icon('heroicon-o-wifi')
                        ->color('info')
                        ->schema([
                            TextInput::make('ssid')->label('SSID')->required(),
                            TextInput::make('password')->label('Wi-Fi password')->required()->minLength(8),
                        ])
                        ->action(function ($record, array $data) {
                            $acs = app(GenieAcsClient::class);
                            $device = $acs->findDeviceBySerial($record->serial_number);
                            if (! $device) {
                                Notification::make()->title('Device not found in ACS')->warning()->send();
                                return;
                            }
                            $ssidOk = $acs->setParameter($device['_id'], 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', $data['ssid']);
                            $pwOk   = $acs->setParameter($device['_id'], 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase', $data['password']);
                            $ok = $ssidOk && $pwOk;
                            Notification::make()->title($ok ? 'Wi-Fi parameters pushed' : 'Push partially failed')->{$ok ? 'success' : 'warning'}()->send();
                        }),
                    Action::make('tr069_view')
                        ->label('View ACS params')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->modalHeading('TR-069 device parameters')
                        ->modalContent(function ($record) {
                            $acs = app(GenieAcsClient::class);
                            $device = $acs->findDeviceBySerial($record->serial_number);
                            if (! $device) {
                                return view('filament.notifications.body', ['body' => 'Device not found in ACS.']);
                            }
                            $informedAt = $device['_lastInform'] ?? 'never';
                            $manufacturer = $device['_deviceId']['_Manufacturer'] ?? '?';
                            $oui = $device['_deviceId']['_OUI'] ?? '?';
                            $productClass = $device['_deviceId']['_ProductClass'] ?? '?';
                            $summary = "Last inform: {$informedAt}\nManufacturer: {$manufacturer}\nOUI: {$oui}\nProductClass: {$productClass}\n\nFull payload (truncated):\n" . substr(json_encode($device, JSON_PRETTY_PRINT), 0, 4000);
                            return '<pre style="white-space:pre-wrap;font-family:monospace;font-size:12px">' . e($summary) . '</pre>';
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                ])->label('TR-069')->icon('heroicon-o-radio')->color('info'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
