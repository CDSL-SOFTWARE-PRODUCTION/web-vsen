<?php

namespace App\Filament\Ops\Pages;

use Filament\Actions\Action;
use App\Filament\Ops\Widgets\BidIntelligenceKpiWidget;
use App\Filament\Ops\Widgets\OpsAdminPortalShortcutsWidget;
use App\Filament\Ops\Widgets\LedgerInflowOutflowChartWidget;
use App\Filament\Ops\Widgets\OpsDebtAndLedgerKpiWidget;
use App\Filament\Ops\Widgets\OpsDemandAndSupplyKpiWidget;
use App\Filament\Ops\Widgets\OpsExecutionAndRiskKpiWidget;
use App\Filament\Ops\Widgets\OpsMilestonesAndLiquidityKpiWidget;
use App\Filament\Ops\Widgets\OrdersCreatedTrendChartWidget;
use App\Filament\Ops\Widgets\RopLowStockTableWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static bool $isDiscovered = false;

    public static function getNavigationLabel(): string
    {
        return __('ops.dashboard.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.dashboard.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboardInfo')
                ->label('Thông tin')
                ->icon('heroicon-m-information-circle')
                ->iconButton()
                ->color('gray')
                ->tooltip(__('ops.dashboard.subheading'))
                ->action(static fn (): null => null),
        ];
    }

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            OpsAdminPortalShortcutsWidget::class,
            OpsExecutionAndRiskKpiWidget::class,
            OrdersCreatedTrendChartWidget::class,
            LedgerInflowOutflowChartWidget::class,
            OpsDemandAndSupplyKpiWidget::class,
            OpsMilestonesAndLiquidityKpiWidget::class,
            OpsDebtAndLedgerKpiWidget::class,
            BidIntelligenceKpiWidget::class,
            RopLowStockTableWidget::class,
        ];
    }

    /**
     * Single column: full-width KPI strips and charts (matches compact SaaS dashboard layouts).
     *
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int|string|array
    {
        return 1;
    }
}
