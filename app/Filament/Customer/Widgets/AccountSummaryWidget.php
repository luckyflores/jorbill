<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AccountSummaryWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $customer = auth('customer')->user();
        if (! $customer instanceof Customer) return [];

        $activeSubs = Subscription::where('customer_id', $customer->id)->where('status', 'active')->count();
        $balanceDue = (int) Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->sum(DB::raw('total_centavos - amount_paid_centavos'));
        $lastPayment = Payment::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->orderByDesc('received_at')
            ->first();
        $overdueCount = Invoice::where('customer_id', $customer->id)->where('status', 'overdue')->count();

        return [
            Stat::make('Active services', $activeSubs)
                ->description($customer->status === 'active' ? 'Account: Active' : "Account: {$customer->status}")
                ->descriptionIcon($customer->status === 'active' ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($customer->status === 'active' ? 'success' : 'warning'),

            Stat::make('Balance due', '₱ ' . number_format($balanceDue / 100, 2))
                ->description($overdueCount > 0 ? "{$overdueCount} overdue invoice(s)" : 'No overdue invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($balanceDue > 0 ? ($overdueCount > 0 ? 'danger' : 'warning') : 'success'),

            Stat::make('Last payment',
                $lastPayment
                    ? '₱ ' . number_format($lastPayment->amount_centavos / 100, 2)
                    : '—'
            )
                ->description($lastPayment ? $lastPayment->received_at?->diffForHumans() : 'No payments yet')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
