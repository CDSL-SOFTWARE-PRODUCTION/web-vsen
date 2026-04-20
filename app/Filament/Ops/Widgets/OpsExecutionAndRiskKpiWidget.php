<?php

namespace App\Filament\Ops\Widgets;

use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Demand\ExecutionIssueResource;
use App\Models\Ops\Contract;
use App\Models\Ops\ExecutionIssue;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpsExecutionAndRiskKpiWidget extends OpsStatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected function getHeading(): ?string
    {
        return __('ops.dashboard.kpi_strip.execution_risk_heading');
    }

    protected function getDescription(): ?string
    {
        return __('ops.dashboard.kpi_strip.execution_risk_description');
    }

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

        $openIssues = ExecutionIssue::query()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->count();

        $red = Contract::query()->where('risk_level', 'Red')->count();
        $amber = Contract::query()->where('risk_level', 'Amber')->count();
        $green = Contract::query()->where('risk_level', 'Green')->count();

        return [
            Stat::make(__('ops.widgets.issues.overdue.title'), (string) $overdue)
                ->description(__('ops.widgets.issues.overdue.description'))
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->url(ExecutionIssueResource::getUrl('index')),
            Stat::make(__('ops.widgets.issues.critical.title'), (string) $critical)
                ->description(__('ops.widgets.issues.critical.description'))
                ->icon('heroicon-o-exclamation-circle')
                ->color('warning')
                ->url(ExecutionIssueResource::getUrl('index')),
            Stat::make(__('ops.widgets.issues.open.title'), (string) $openIssues)
                ->description(__('ops.widgets.issues.open.description'))
                ->icon('heroicon-o-queue-list')
                ->color('info')
                ->url(ExecutionIssueResource::getUrl('index')),
            Stat::make(__('ops.widgets.contracts.red.title'), (string) $red)
                ->description(__('ops.widgets.contracts.red.description'))
                ->icon('heroicon-o-face-frown')
                ->color('danger')
                ->url(ContractResource::getUrl('index')),
            Stat::make(__('ops.widgets.contracts.amber.title'), (string) $amber)
                ->description(__('ops.widgets.contracts.amber.description'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->url(ContractResource::getUrl('index')),
            Stat::make(__('ops.widgets.contracts.green.title'), (string) $green)
                ->description(__('ops.widgets.contracts.green.description'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->url(ContractResource::getUrl('index')),
        ];
    }
}
