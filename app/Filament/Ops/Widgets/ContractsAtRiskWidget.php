<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContractsAtRiskWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $red = Contract::query()->where('risk_level', 'Red')->count();
        $amber = Contract::query()->where('risk_level', 'Amber')->count();
        $green = Contract::query()->where('risk_level', 'Green')->count();

        return [
            Stat::make(__('ops.widgets.contracts.red.title'), (string) $red)
                ->description(__('ops.widgets.contracts.red.description'))
                ->color('danger'),
            Stat::make(__('ops.widgets.contracts.amber.title'), (string) $amber)
                ->description(__('ops.widgets.contracts.amber.description'))
                ->color('warning'),
            Stat::make(__('ops.widgets.contracts.green.title'), (string) $green)
                ->description(__('ops.widgets.contracts.green.description'))
                ->color('success'),
        ];
    }
}
