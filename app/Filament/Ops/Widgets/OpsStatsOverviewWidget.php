<?php

namespace App\Filament\Ops\Widgets;

use Filament\Widgets\StatsOverviewWidget;

/**
 * Uses Filament's default stat grid (typically 3–4 columns) so KPIs read as a compact horizontal strip,
 * aligned with common Filament dashboard layouts. Dashboard page uses a 2-column grid on large screens;
 * each widget spans one column unless overridden.
 */
abstract class OpsStatsOverviewWidget extends StatsOverviewWidget
{
    /**
     * @var view-string
     */
    protected static string $view = 'filament.ops.widgets.stats-overview-widget';

    /**
     * @var int | string | array<string, int | string | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * Single-stat widgets must use one column; Filament's default uses a 3-column grid when there are
     * fewer than three stats, which leaves empty space (e.g. Demand with one open-orders KPI).
     */
    protected function getColumns(): int
    {
        $count = count($this->getCachedStats());

        if ($count === 1) {
            return 1;
        }

        return parent::getColumns();
    }
}
