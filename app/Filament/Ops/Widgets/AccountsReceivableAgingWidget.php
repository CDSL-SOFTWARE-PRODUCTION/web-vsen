<?php

namespace App\Filament\Ops\Widgets;

use App\Domain\Finance\MilestoneAgingService;
use App\Models\Ops\FinancialLedgerEntry;
use App\Models\Ops\PaymentMilestone;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class AccountsReceivableAgingWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        if (! Schema::hasColumn('payment_milestones', 'days_overdue_cached')) {
            return [
                Stat::make(__('ops.widgets.ar.overdue_milestones.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->color('gray'),
                Stat::make(__('ops.widgets.ar.overdue_amount.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->color('gray'),
                Stat::make(__('ops.widgets.ar.ledger_inflow.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->color('gray'),
            ];
        }

        app(MilestoneAgingService::class)->refreshAllCachedOverdue();

        $overdueSum = PaymentMilestone::query()
            ->where('days_overdue_cached', '>', 0)
            ->sum('amount_planned');

        $overdueCount = PaymentMilestone::query()
            ->where('days_overdue_cached', '>', 0)
            ->count();

        $inflow = FinancialLedgerEntry::query()
            ->where('type', 'Inflow')
            ->sum('amount');

        return [
            Stat::make(__('ops.widgets.ar.overdue_milestones.title'), (string) $overdueCount)
                ->description(__('ops.widgets.ar.overdue_milestones.description'))
                ->color($overdueCount > 0 ? 'danger' : 'success'),
            Stat::make(__('ops.widgets.ar.overdue_amount.title'), number_format((float) $overdueSum, 0, '.', ',').' VND')
                ->description(__('ops.widgets.ar.overdue_amount.description'))
                ->color('warning'),
            Stat::make(__('ops.widgets.ar.ledger_inflow.title'), number_format((float) $inflow, 0, '.', ',').' VND')
                ->description(__('ops.widgets.ar.ledger_inflow.description'))
                ->color('info'),
        ];
    }
}
