<?php

namespace App\Filament\Pages;

use App\Models\RadiusSession;
use App\Services\Network\RadiusManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LiveSessions extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Network';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-signal';
    protected static ?string $title = 'Live Sessions';
    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.live-sessions';

    public function getSessions()
    {
        return RadiusSession::active()
            ->orderBy('acctstarttime', 'desc')
            ->limit(200)
            ->get();
    }

    public function kick(int $radacctId): void
    {
        $session = RadiusSession::find($radacctId);
        if (! $session) {
            Notification::make()->title('Session not found')->danger()->send();
            return;
        }

        $ok = app(RadiusManager::class)->kickSession($session);
        Notification::make()
            ->title($ok ? 'Disconnect-ACK received' : 'Kick failed (see logs)')
            ->{$ok ? 'success' : 'danger'}()
            ->send();
    }
}
