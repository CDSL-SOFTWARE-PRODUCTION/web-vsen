<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Supply\SupplyOrderLine;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplySelectionOverviewWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $baseQuery = SupplyOrderLine::query();

        $total = (clone $baseQuery)->count();
        $autoSuggested = (clone $baseQuery)->where('supplier_selection_mode', 'auto_suggested')->count();
        $manualOverride = (clone $baseQuery)->where('supplier_selection_mode', 'manual_override')->count();
        $unknownMode = (clone $baseQuery)->whereNull('supplier_selection_mode')->count();

        $sourceByIdentifier = (clone $baseQuery)->where('supplier_suggestion_source', 'bidder_identifier')->count();
        $sourceByName = (clone $baseQuery)->where('supplier_suggestion_source', 'bidder_name')->count();

        return [
            Stat::make(__('ops.supply_selection_analysis.metrics.total'), number_format($total, 0, ',', '.')),
            Stat::make(__('ops.supply_selection_analysis.metrics.auto_suggested'), number_format($autoSuggested, 0, ',', '.'))
                ->description(__('ops.supply_selection_analysis.metrics.auto_suggested_desc')),
            Stat::make(__('ops.supply_selection_analysis.metrics.manual_override'), number_format($manualOverride, 0, ',', '.'))
                ->description(__('ops.supply_selection_analysis.metrics.manual_override_desc')),
            Stat::make(__('ops.supply_selection_analysis.metrics.unknown_mode'), number_format($unknownMode, 0, ',', '.')),
            Stat::make(__('ops.supply_selection_analysis.metrics.source_identifier'), number_format($sourceByIdentifier, 0, ',', '.')),
            Stat::make(__('ops.supply_selection_analysis.metrics.source_name'), number_format($sourceByName, 0, ',', '.')),
        ];
    }
}
