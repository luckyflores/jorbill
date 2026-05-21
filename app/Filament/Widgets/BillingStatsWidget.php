<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BillingStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $mrrCents = DB::table('subscriptions')
            ->join('services', 'subscriptions.service_id', '=', 'services.id')
            ->where('subscriptions.status', 'active')
            ->whereNull('subscriptions.deleted_at')
            ->whereNull('services.deleted_at')
            ->sum(DB::raw('COALESCE(subscriptions.price_centavos_override, services.price_centavos)'));
        $mrr = '₱ ' . number_format($mrrCents / 100, 2);

        $overdueCount = Invoice::where('status', 'overdue')->count();
        $overdueAmount = Invoice::where('status', 'overdue')
            ->sum(DB::raw('total_centavos - amount_paid_centavos'));

        $thisMonthPayments = Payment::where('status', 'completed')
            ->whereMonth('received_at', now()->month)
            ->whereYear('received_at', now()->year)
            ->sum('amount_centavos');

        $outstanding = Invoice::whereIn('status', ['issued', 'overdue'])
            ->sum(DB::raw('total_centavos - amount_paid_centavos'));

        return [
            Stat::make('MRR (active subs)', $mrr)
                ->description('Monthly recurring revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Overdue invoices', $overdueCount)
                ->description('₱ ' . number_format($overdueAmount / 100, 2) . ' due')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Payments this month', '₱ ' . number_format($thisMonthPayments / 100, 2))
                ->description(Payment::where('status', 'completed')
                    ->whereMonth('received_at', now()->month)->count() . ' transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Outstanding balance', '₱ ' . number_format($outstanding / 100, 2))
                ->description('Issued + overdue')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}
