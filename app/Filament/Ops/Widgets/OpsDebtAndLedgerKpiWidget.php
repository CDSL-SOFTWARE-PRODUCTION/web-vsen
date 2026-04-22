<?php

namespace App\Filament\Ops\Widgets;

use App\Domain\Finance\MilestoneAgingService;
use App\Filament\Ops\Clusters\Finance\Resources\FinancialLedgerEntryResource;
use App\Models\Ops\FinancialLedgerEntry;
use App\Models\Ops\PaymentMilestone;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class OpsDebtAndLedgerKpiWidget extends OpsStatsOverviewWidget
{
    protected static ?int $sort = 45;

    protected function getHeading(): ?string
    {
        return __('ops.dashboard.kpi_strip.debt_ledger_heading');
    }

    protected function getDescription(): ?string
    {
        return __('ops.dashboard.kpi_strip.debt_ledger_description');
    }

    protected function getStats(): array
    {
        $arStats = $this->accountsReceivableStats();

        if (! $this->canViewFounderLedger()) {
            return $arStats;
        }

        $since = now()->subDays(30);
        $inflow = (float) FinancialLedgerEntry::query()
            ->where('created_at', '>=', $since)
            ->where('amount', '>', 0)
            ->sum('amount');
        $outflow = abs((float) FinancialLedgerEntry::query()
            ->where('created_at', '>=', $since)
            ->where('amount', '<', 0)
            ->sum('amount'));

        return array_merge($arStats, [
            Stat::make(__('ops.widgets.founder.inflow_30d'), number_format($inflow, 0, '.', ',').' VND')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success')
                ->url(FinancialLedgerEntryResource::getUrl('index', ['activeTab' => 'inflows'])),
            Stat::make(__('ops.widgets.founder.outflow_30d'), number_format($outflow, 0, '.', ',').' VND')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('danger')
                ->url(FinancialLedgerEntryResource::getUrl('index', ['activeTab' => 'outflows'])),
        ]);
    }

    private function canViewFounderLedger(): bool
    {
        return auth()->user()?->role === 'Admin_PM';
    }

    /**
     * @return array<int, Stat>
     */
    private function accountsReceivableStats(): array
    {
        if (! Schema::hasColumn('payment_milestones', 'days_overdue_cached')) {
            return [
                Stat::make(__('ops.widgets.ar.overdue_milestones.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->icon('heroicon-o-calendar-days')
                    ->color('gray'),
                Stat::make(__('ops.widgets.ar.overdue_amount.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->icon('heroicon-o-currency-dollar')
                    ->color('gray'),
                Stat::make(__('ops.widgets.ar.ledger_inflow.title'), '—')
                    ->description(__('ops.widgets.ar.migration_pending'))
                    ->icon('heroicon-o-arrow-down-circle')
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
                ->icon('heroicon-o-calendar-days')
                ->color($overdueCount > 0 ? 'danger' : 'success'),
            Stat::make(__('ops.widgets.ar.overdue_amount.title'), number_format((float) $overdueSum, 0, '.', ',').' VND')
                ->description(__('ops.widgets.ar.overdue_amount.description'))
                ->icon('heroicon-o-currency-dollar')
                ->color('warning'),
            Stat::make(__('ops.widgets.ar.ledger_inflow.title'), number_format((float) $inflow, 0, '.', ',').' VND')
                ->description(__('ops.widgets.ar.ledger_inflow.description'))
                ->icon('heroicon-o-arrow-down-circle')
                ->color('info'),
        ];
    }
}
