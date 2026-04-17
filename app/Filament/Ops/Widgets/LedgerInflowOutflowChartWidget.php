<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Ops\FinancialLedgerEntry;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Grouped bar: daily inflow vs outflow (last 14 days) from ledger rows.
 */
class LedgerInflowOutflowChartWidget extends ChartWidget
{
    protected static ?int $sort = 16;

    protected static ?string $maxHeight = '280px';

    protected static string $color = 'gray';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->role === 'Admin_PM';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('ops.dashboard.charts.ledger_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('ops.dashboard.charts.ledger_description');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $inflow = [];
        $outflow = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $date = $day->toDateString();
            $labels[] = $day->format('d/m');

            $inflow[] = round((float) FinancialLedgerEntry::query()
                ->whereDate('created_at', $date)
                ->where('amount', '>', 0)
                ->sum('amount'), 2);

            $outflow[] = round(abs((float) FinancialLedgerEntry::query()
                ->whereDate('created_at', $date)
                ->where('amount', '<', 0)
                ->sum('amount')), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => __('ops.dashboard.charts.inflow'),
                    'data' => $inflow,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.45)',
                    'borderColor' => 'rgb(22, 163, 74)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => __('ops.dashboard.charts.outflow'),
                    'data' => $outflow,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.45)',
                    'borderColor' => 'rgb(220, 38, 38)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
