<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Demand\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Sale workspace: open orders (not closed/abandoned).
 */
class SalePipelineWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $r = auth()->user()?->role;

        return in_array($r, ['Sale', 'Admin_PM'], true);
    }

    protected function getStats(): array
    {
        $open = Order::query()
            ->whereNotIn('state', ['ContractClosed', 'Abandoned'])
            ->count();

        return [
            Stat::make(__('ops.widgets.sale.open_orders'), (string) $open)
                ->description(__('ops.widgets.sale.open_orders_desc'))
                ->color('primary'),
        ];
    }
}
