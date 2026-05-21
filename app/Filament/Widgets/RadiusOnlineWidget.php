<?php

namespace App\Filament\Widgets;

use App\Models\RadiusSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RadiusOnlineWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'RADIUS';

    protected function getStats(): array
    {
        $online = RadiusSession::active()->count();
        $inGb   = round((RadiusSession::active()->sum('acctinputoctets')  ?? 0) / 1024 / 1024 / 1024, 2);
        $outGb  = round((RadiusSession::active()->sum('acctoutputoctets') ?? 0) / 1024 / 1024 / 1024, 2);
        $todayLogins = DB::table('radpostauth')
            ->whereDate('authdate', today())
            ->where('reply', 'Access-Accept')
            ->count();

        return [
            Stat::make('Online now', $online)
                ->description('Active sessions (RADIUS)')
                ->descriptionIcon('heroicon-m-signal')
                ->color($online > 0 ? 'success' : 'gray'),

            Stat::make('In (active)', $inGb . ' GB')
                ->description('Across all live sessions')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Out (active)', $outGb . ' GB')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('primary'),

            Stat::make('Logins today', $todayLogins)
                ->description('Successful Access-Accept')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning'),
        ];
    }
}
