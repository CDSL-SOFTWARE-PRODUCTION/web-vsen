<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\ExecutionIssue;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverdueIssuesWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $overdue = ExecutionIssue::query()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->where('due_at', '<', now())
            ->count();

        $critical = ExecutionIssue::query()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->where('severity', 'Critical')
            ->count();

        $open = ExecutionIssue::query()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->count();

        return [
            Stat::make(__('ops.widgets.issues.overdue.title'), (string) $overdue)
                ->description(__('ops.widgets.issues.overdue.description'))
                ->color('danger'),
            Stat::make(__('ops.widgets.issues.critical.title'), (string) $critical)
                ->description(__('ops.widgets.issues.critical.description'))
                ->color('warning'),
            Stat::make(__('ops.widgets.issues.open.title'), (string) $open)
                ->description(__('ops.widgets.issues.open.description'))
                ->color('info'),
        ];
    }
}
