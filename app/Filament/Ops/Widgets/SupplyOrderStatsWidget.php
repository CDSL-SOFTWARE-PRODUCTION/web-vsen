<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Supply\SupplyOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplyOrderStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $total = SupplyOrder::query()->count();
        $inProgress = SupplyOrder::query()->whereNotIn('status', ['Received'])->count();
        $draftOrOpen = SupplyOrder::query()->whereIn('status', ['Draft', 'Open'])->count();

        return [
            Stat::make(__('ops.supply_order.stats.total'), (string) $total)
                ->description(__('ops.supply_order.stats.total_desc'))
                ->color('gray'),
            Stat::make(__('ops.supply_order.stats.in_progress'), (string) $inProgress)
                ->description(__('ops.supply_order.stats.in_progress_desc'))
                ->color($inProgress > 0 ? 'warning' : 'success'),
            Stat::make(__('ops.supply_order.stats.draft_open'), (string) $draftOrOpen)
                ->description(__('ops.supply_order.stats.draft_open_desc'))
                ->color('info'),
        ];
    }
}
