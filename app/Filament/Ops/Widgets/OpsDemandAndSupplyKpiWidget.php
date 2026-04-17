<?php

namespace App\Filament\Ops\Widgets;

use App\Filament\Ops\Resources\OrderResource;
use App\Filament\Ops\Resources\SupplyOrderResource;
use App\Models\Demand\Order;
use App\Models\Supply\SupplyOrder;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpsDemandAndSupplyKpiWidget extends OpsStatsOverviewWidget
{
    protected static ?int $sort = 25;

    protected function getHeading(): ?string
    {
        return __('ops.dashboard.kpi_strip.demand_supply_heading');
    }

    protected function getDescription(): ?string
    {
        return __('ops.dashboard.kpi_strip.demand_supply_description');
    }

    protected function getStats(): array
    {
        $total = SupplyOrder::query()->count();
        $inProgress = SupplyOrder::query()->whereNotIn('status', ['Received'])->count();
        $draftOrOpen = SupplyOrder::query()->whereIn('status', ['Draft', 'Open'])->count();

        $supplyStats = [
            Stat::make(__('ops.supply_order.stats.total'), (string) $total)
                ->description(__('ops.supply_order.stats.total_desc'))
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->url(SupplyOrderResource::getUrl('index')),
            Stat::make(__('ops.supply_order.stats.in_progress'), (string) $inProgress)
                ->description(__('ops.supply_order.stats.in_progress_desc'))
                ->icon('heroicon-o-arrow-path')
                ->color($inProgress > 0 ? 'warning' : 'success')
                ->url(SupplyOrderResource::getUrl('index')),
            Stat::make(__('ops.supply_order.stats.draft_open'), (string) $draftOrOpen)
                ->description(__('ops.supply_order.stats.draft_open_desc'))
                ->icon('heroicon-o-inbox')
                ->color('info')
                ->url(SupplyOrderResource::getUrl('index')),
        ];

        if (! $this->canViewSaleKpi()) {
            return $supplyStats;
        }

        $open = Order::query()
            ->whereNotIn('state', ['ContractClosed', 'Abandoned'])
            ->count();

        $demandStat = Stat::make(__('ops.widgets.sale.open_orders'), (string) $open)
            ->description(__('ops.widgets.sale.open_orders_desc'))
            ->icon('heroicon-o-shopping-cart')
            ->color('primary')
            ->url(OrderResource::getUrl('index'));

        return array_merge([$demandStat], $supplyStats);
    }

    private function canViewSaleKpi(): bool
    {
        $r = auth()->user()?->role;

        return in_array($r, ['Sale', 'Admin_PM'], true);
    }
}
