<?php

namespace App\Filament\Pages;

use App\Services\Odoo\Contracts\OdooClient;
use App\Services\Odoo\Live\LiveOdooClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OdooSettings extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cloud';
    protected static ?string $title = 'Odoo Settings';
    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.odoo-settings';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Test connection')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action(function () {
                    // Always test against current .env config — use a fresh Live instance
                    $client = new LiveOdooClient(
                        baseUrl:  config('odoo.base_url'),
                        db:       config('odoo.db'),
                        login:    config('odoo.login'),
                        password: config('odoo.password'),
                    );
                    $result = $client->testConnection();
                    if ($result['ok']) {
                        Notification::make()
                            ->title('Connected to Odoo ✓')
                            ->body("uid: {$result['uid']} · server: {$result['server_version']}")
                            ->success()->persistent()->send();
                    } else {
                        Notification::make()
                            ->title('Connection failed')
                            ->body($result['error'] ?? 'unknown error')
                            ->danger()->persistent()->send();
                    }
                }),
        ];
    }
}
