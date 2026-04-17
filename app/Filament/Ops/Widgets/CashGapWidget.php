<?php

namespace App\Filament\Ops\Widgets;

use App\Filament\Ops\Resources\CashPlanEventResource;
use App\Filament\Ops\Resources\ContractResource;
use App\Filament\Ops\Resources\PaymentMilestoneResource;
use App\Models\Ops\CashPlanEvent;
use App\Models\Ops\Contract;
use App\Models\Ops\PaymentMilestone;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashGapWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $start = now()->toDateString();
        $end = now()->addDays(14)->toDateString();

        $cashNeed14d = CashPlanEvent::query()
            ->whereBetween('scheduled_date', [$start, $end])
            ->sum('amount');

        $milestoneDue14d = PaymentMilestone::query()
            ->whereBetween('due_date', [$start, $end])
            ->sum('amount_planned');

        $allocatedBudget = Contract::query()->sum('allocated_budget');

        $gap = (float) $cashNeed14d - (float) $allocatedBudget;

        return [
            Stat::make(__('ops.widgets.cash.need_14d.title'), number_format((float) $cashNeed14d, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.need_14d.description'))
                ->color('info')
                ->url(CashPlanEventResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.milestones_14d.title'), number_format((float) $milestoneDue14d, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.milestones_14d.description'))
                ->color('warning')
                ->url(PaymentMilestoneResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.allocated.title'), number_format((float) $allocatedBudget, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.allocated.description'))
                ->color('gray')
                ->url(ContractResource::getUrl('index')),
            Stat::make(__('ops.widgets.cash.gap.title'), number_format((float) $gap, 0, '.', ',').' VND')
                ->description(__('ops.widgets.cash.gap.description'))
                ->color($gap > 0 ? 'danger' : 'success')
                ->url(CashPlanEventResource::getUrl('index')),
        ];
    }
}
