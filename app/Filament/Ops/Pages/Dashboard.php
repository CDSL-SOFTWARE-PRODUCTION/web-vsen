<?php

namespace App\Filament\Ops\Pages;

use App\Filament\Ops\Widgets\OpsDebtAndLedgerKpiWidget;
use App\Filament\Ops\Widgets\OpsDemandAndSupplyKpiWidget;
use App\Filament\Ops\Widgets\OpsExecutionAndRiskKpiWidget;
use App\Filament\Ops\Widgets\OpsMilestonesAndLiquidityKpiWidget;
use Filament\Actions\Action;
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
            OpsExecutionAndRiskKpiWidget::class,
            OpsDemandAndSupplyKpiWidget::class,
            OpsMilestonesAndLiquidityKpiWidget::class,
            OpsDebtAndLedgerKpiWidget::class,
        ];
    }

    /**
     * One column so each KPI strip stays full width; charts and deep tables live on their own screens.
     *
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int|string|array
    {
        return 1;
    }
}
