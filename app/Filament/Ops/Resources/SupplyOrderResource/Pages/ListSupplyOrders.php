<?php

namespace App\Filament\Ops\Resources\SupplyOrderResource\Pages;

use App\Filament\Ops\Resources\SupplyOrderResource;
use App\Filament\Ops\Widgets\SupplyOrderStatsWidget;
use App\Models\Supply\SupplyOrder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSupplyOrders extends ListRecords
{
    protected static string $resource = SupplyOrderResource::class;

    /**
     * Ensure the list table never sees an Eloquent builder without a model instance (avoids
     * Table::getModel() => "Cannot use ::class on null" on filters / exports).
     */
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        return $query->getModel() === null ? SupplyOrderResource::getEloquentQuery() : $query;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('ops.supply_order.tabs.all'))
                ->badge(SupplyOrder::query()->count()),
            'in_progress' => Tab::make(__('ops.supply_order.tabs.in_progress'))
                ->badge(SupplyOrder::query()->whereNotIn('status', ['Received'])->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotIn('status', ['Received'])),
            'draft_open' => Tab::make(__('ops.supply_order.tabs.draft_open'))
                ->badge(SupplyOrder::query()->whereIn('status', ['Draft', 'Open'])->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', ['Draft', 'Open'])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SupplyOrderStatsWidget::class,
        ];
    }
}
