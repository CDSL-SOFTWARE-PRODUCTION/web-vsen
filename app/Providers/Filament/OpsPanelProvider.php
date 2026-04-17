<?php

namespace App\Providers\Filament;

use App\Filament\Ops\Pages\Dashboard as OpsDashboard;
use App\Filament\Ops\Widgets\LedgerInflowOutflowChartWidget;
use App\Filament\Ops\Widgets\OpsDebtAndLedgerKpiWidget;
use App\Filament\Ops\Widgets\OpsDemandAndSupplyKpiWidget;
use App\Filament\Ops\Widgets\OpsExecutionAndRiskKpiWidget;
use App\Filament\Ops\Widgets\OpsMilestonesAndLiquidityKpiWidget;
use App\Filament\Ops\Widgets\OrdersCreatedTrendChartWidget;
use App\Filament\Ops\Widgets\RopLowStockTableWidget;
use App\Filament\Ops\Widgets\SupplyOrderStatsWidget;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OpsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ops')
            ->path('ops')
            ->login()
            ->profile()
            ->darkMode(true, false)
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverClusters(in: app_path('Filament/Ops/Clusters'), for: 'App\\Filament\\Ops\\Clusters')
            ->discoverResources(in: app_path('Filament/Ops/Resources'), for: 'App\\Filament\\Ops\\Resources')
            ->discoverPages(in: app_path('Filament/Ops/Pages'), for: 'App\\Filament\\Ops\\Pages')
            ->pages([
                OpsDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Ops/Widgets'), for: 'App\\Filament\\Ops\\Widgets')
            ->widgets([
                OpsExecutionAndRiskKpiWidget::class,
                OrdersCreatedTrendChartWidget::class,
                LedgerInflowOutflowChartWidget::class,
                OpsDemandAndSupplyKpiWidget::class,
                OpsMilestonesAndLiquidityKpiWidget::class,
                OpsDebtAndLedgerKpiWidget::class,
                SupplyOrderStatsWidget::class,
                RopLowStockTableWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(__('ops.nav_groups.master_data'))
                    ->icon('heroicon-o-circle-stack')
                    ->collapsed(),
                NavigationGroup::make(__('ops.nav_groups.system'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => Blade::render('
                    <div class="flex items-center gap-x-3 mr-3">
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'vi\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'vi\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                            VI
                        </a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'en\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'en\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                            EN
                        </a>
                    </div>
                '),
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => '<link rel="stylesheet" href="'.e(asset('css/ops-panel.css')).'?v=1">',
            );
    }
}
