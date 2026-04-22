<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Demand\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Line chart: new orders per day (last 14 days). Complements KPI cards with a time trend.
 */
class OrdersCreatedTrendChartWidget extends ChartWidget
{
    /**
     * @var view-string
     */
    protected static string $view = 'filament.ops.widgets.chart-widget';

    protected static ?int $sort = 15;

    protected static ?string $maxHeight = '260px';

    protected static string $color = 'primary';

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|Htmlable|null
    {
        return __('ops.dashboard.charts.orders_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ops.dashboard.charts.orders_description');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $labels = [];
        $counts = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $labels[] = $day->format('d/m');
            $counts[] = Order::query()
                ->whereDate('created_at', $day->toDateString())
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('ops.dashboard.charts.orders_series'),
                    'data' => $counts,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
