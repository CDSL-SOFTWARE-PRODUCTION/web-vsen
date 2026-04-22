<?php

namespace App\Filament\Ops\Widgets;

use App\Filament\Ops\Clusters\Supply\Resources\SupplyOrderResource;
use App\Models\Supply\SupplyOrder;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplyOrderStatsWidget extends OpsStatsOverviewWidget
{
    protected static ?int $sort = 30;

    protected function getHeading(): ?string
    {
        return __('ops.dashboard.sections.supply');
    }

    protected function getDescription(): ?string
    {
        return __('ops.dashboard.sections.supply_desc');
    }

    protected function getStats(): array
    {
        $total = SupplyOrder::query()->count();
        $inProgress = SupplyOrder::query()->whereNotIn('status', ['Received'])->count();
        $draftOrOpen = SupplyOrder::query()->whereIn('status', ['Draft', 'Open', 'PendingApproval'])->count();

        return [
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
    }
}
