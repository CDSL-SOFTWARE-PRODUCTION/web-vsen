<?php

namespace App\Providers\Filament;

use App\Filament\Ops\Pages\Dashboard as OpsDashboard;
use App\Filament\Ops\Widgets\OpsDebtAndLedgerKpiWidget;
use App\Filament\Ops\Widgets\OpsDemandAndSupplyKpiWidget;
use App\Filament\Ops\Widgets\OpsExecutionAndRiskKpiWidget;
use App\Filament\Ops\Widgets\OpsMilestonesAndLiquidityKpiWidget;
use App\Http\Middleware\FilamentAuthenticateRedirectToLogin;
use App\Http\Middleware\SetLocale;
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
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OpsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ops')
            ->path('ops')
            ->homeUrl(fn (): string => route('dashboard'))
            ->profile()
            ->darkMode(true, false)
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Ops/Resources'), for: 'App\\Filament\\Ops\\Resources')
            ->discoverPages(in: app_path('Filament/Ops/Pages'), for: 'App\\Filament\\Ops\\Pages')
            ->pages([
                OpsDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Ops/Widgets'), for: 'App\\Filament\\Ops\\Widgets')
            ->widgets([
                OpsExecutionAndRiskKpiWidget::class,
                OpsDemandAndSupplyKpiWidget::class,
                OpsMilestonesAndLiquidityKpiWidget::class,
                OpsDebtAndLedgerKpiWidget::class,
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
                FilamentAuthenticateRedirectToLogin::class,
            ], isPersistent: true)
            ->navigationGroups([
                NavigationGroup::make(__('ops.clusters.demand'))
                    ->icon('heroicon-o-document-text')
                    ->collapsed(),
                NavigationGroup::make(__('ops.clusters.master_data'))
                    ->icon('heroicon-o-circle-stack')
                    ->collapsed(),
                NavigationGroup::make(__('ops.clusters.supply'))
                    ->icon('heroicon-o-shopping-cart')
                    ->collapsed(),
                NavigationGroup::make(__('ops.clusters.inventory'))
                    ->icon('heroicon-o-archive-box')
                    ->collapsed(),
                NavigationGroup::make(__('ops.clusters.delivery'))
                    ->icon('heroicon-o-truck')
                    ->collapsed(),
                NavigationGroup::make(__('ops.clusters.finance'))
                    ->icon('heroicon-o-banknotes')
                    ->collapsed(),
                NavigationGroup::make(__('ops.nav_groups.system'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => PanelLocaleSwitcher::render(),
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => '<link rel="stylesheet" href="'.e(asset('css/ops-panel.css')).'?v=1">',
            );
    }
}
