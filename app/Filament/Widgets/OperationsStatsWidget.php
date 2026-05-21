<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Subscription;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Customers', Customer::count())
                ->description(Customer::where('status', 'active')->count() . ' active')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Active subscriptions', Subscription::where('status', 'active')->count())
                ->description(Subscription::where('status', 'suspended')->count() . ' suspended')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('success'),

            Stat::make('Pending job orders', JobOrder::whereIn('status', ['pending', 'dispatched'])->count())
                ->description(JobOrder::where('status', 'in_progress')->count() . ' in progress')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Open tickets', Ticket::whereIn('status', ['open', 'pending'])->count())
                ->description(Ticket::where('priority', 'urgent')->whereIn('status', ['open', 'pending'])->count() . ' urgent')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('danger'),
        ];
    }
}
