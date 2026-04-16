<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\CashPlanEvent;
use App\Models\Ops\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashGapWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $cashNeed14d = CashPlanEvent::query()
            ->whereBetween('scheduled_date', [now()->toDateString(), now()->addDays(14)->toDateString()])
            ->sum('amount');

        $allocatedBudget = Contract::query()->sum('allocated_budget');

        $gap = $cashNeed14d - $allocatedBudget;

        return [
            Stat::make(__('ops.widgets.cash.need_14d.title'), number_format((float) $cashNeed14d, 0, '.', ',') . ' VND')
                ->description(__('ops.widgets.cash.need_14d.description'))
                ->color('info'),
            Stat::make(__('ops.widgets.cash.allocated.title'), number_format((float) $allocatedBudget, 0, '.', ',') . ' VND')
                ->description(__('ops.widgets.cash.allocated.description'))
                ->color('gray'),
            Stat::make(__('ops.widgets.cash.gap.title'), number_format((float) $gap, 0, '.', ',') . ' VND')
                ->description(__('ops.widgets.cash.gap.description'))
                ->color($gap > 0 ? 'danger' : 'success'),
        ];
    }
}
