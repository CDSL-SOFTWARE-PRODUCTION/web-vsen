<?php

namespace App\Filament\Ops\Widgets;

use App\Filament\Ops\Resources\CashPlanEventResource;
use App\Filament\Ops\Resources\ContractResource;
use App\Filament\Ops\Resources\PaymentMilestoneResource;
use App\Models\Ops\CashPlanEvent;
use App\Models\Ops\Contract;
use App\Models\Ops\PaymentMilestone;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpsMilestonesAndLiquidityKpiWidget extends OpsStatsOverviewWidget
{
    protected static ?int $sort = 35;

    protected function getHeading(): ?string
    {
        return __('ops.dashboard.kpi_strip.milestones_liquidity_heading');
    }

    protected function getDescription(): ?string
    {
        return __('ops.dashboard.kpi_strip.milestones_liquidity_description');
    }

    protected function getStats(): array
    {
        $blocked7d = PaymentMilestone::query()
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->where('checklist_status', '!=', 'complete')
            ->count();

        $blocked30d = PaymentMilestone::query()
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->where('checklist_status', '!=', 'complete')
            ->count();

        $ready = PaymentMilestone::query()
            ->where('payment_ready', true)
            ->count();

        $start = now()->toDateString();
        $end = now()->addDays(14)->toDateString();

        $cashNeed14d = CashPlanEvent::query()
            ->whereBetween('scheduled_date', [$start, $end])
            ->sum('amount');

        $milestoneDue14d = PaymentMilestone::query()
            ->whereBetween('due_date', [$start, $end])
            ->sum('amount_planned');

        $allocatedBudget = Contract::query()->sum('allocated_budget');

        return [
            Stat::make(__('ops.widgets.milestones.blocked_7d.title'), (string) $blocked7d)
                ->description(__('ops.widgets.milestones.blocked_7d.description'))
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->url(PaymentMilestoneResource::getUrl('index')),
            Stat::make(__('ops.widgets.milestones.blocked_30d.title'), (string) $blocked30d)
                ->description(__('ops.widgets.milestones.blocked_30d.description'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->url(PaymentMilestoneResource::getUrl('index')),
            Stat::make(__('ops.widgets.milestones.ready.title'), (string) $ready)
                ->description(__('ops.widgets.milestones.ready.description'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(PaymentMilestoneResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.need_14d.title'), number_format((float) $cashNeed14d, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.need_14d.description'))
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->url(CashPlanEventResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.milestones_14d.title'), number_format((float) $milestoneDue14d, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.milestones_14d.description'))
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->url(PaymentMilestoneResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.allocated.title'), number_format((float) $allocatedBudget, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.allocated.description'))
                ->icon('heroicon-o-scale')
                ->color('gray')
                ->url(ContractResource::getUrl('index')),
        ];
    }
}
