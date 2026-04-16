<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\PaymentMilestone;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MilestonesBlockedWidget extends StatsOverviewWidget
{
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

        return [
            Stat::make(__('ops.widgets.milestones.blocked_7d.title'), (string) $blocked7d)
                ->description(__('ops.widgets.milestones.blocked_7d.description'))
                ->color('danger'),
            Stat::make(__('ops.widgets.milestones.blocked_30d.title'), (string) $blocked30d)
                ->description(__('ops.widgets.milestones.blocked_30d.description'))
                ->color('warning'),
            Stat::make(__('ops.widgets.milestones.ready.title'), (string) $ready)
                ->description(__('ops.widgets.milestones.ready.description'))
                ->color('success'),
        ];
    }
}
