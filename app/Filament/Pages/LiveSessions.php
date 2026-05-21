<?php

namespace App\Filament\Pages;

use App\Models\RadiusSession;
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
}
