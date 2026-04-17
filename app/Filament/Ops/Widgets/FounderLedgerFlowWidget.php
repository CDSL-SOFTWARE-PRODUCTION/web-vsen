<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\FinancialLedgerEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Simplified cash movement view (Sankey-style backlog: use ledger sums).
 */
class FounderLedgerFlowWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->role === 'Admin_PM';
    }

    protected function getStats(): array
    {
        $since = now()->subDays(30);
        $inflow = (float) FinancialLedgerEntry::query()
            ->where('created_at', '>=', $since)
            ->where('amount', '>', 0)
            ->sum('amount');
        $outflow = abs((float) FinancialLedgerEntry::query()
            ->where('created_at', '>=', $since)
            ->where('amount', '<', 0)
            ->sum('amount'));

        return [
            Stat::make(__('ops.widgets.founder.inflow_30d'), number_format($inflow, 0, '.', ',').' VND')
                ->color('success'),
            Stat::make(__('ops.widgets.founder.outflow_30d'), number_format($outflow, 0, '.', ',').' VND')
                ->color('danger'),
        ];
    }
}
