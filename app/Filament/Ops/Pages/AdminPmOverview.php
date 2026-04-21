<?php

namespace App\Filament\Ops\Pages;

use App\Support\Ops\FilamentAccess;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Lightweight Ops landing for {@see FilamentAccess::isAdminPm()} — links & context, not the full KPI dashboard.
 */
class AdminPmOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.ops.pages.admin-pm-overview';

    protected static ?int $navigationSort = -200;

    public string $dataStewardUrl = '';

    public string $cmsUrl = '';

    public string $fullDashboardUrl = '';

    public function mount(): void
    {
        $this->dataStewardUrl = Filament::getPanel('data-steward')->getUrl() ?? url('/data-steward');
        $this->cmsUrl = Filament::getPanel('cms')->getUrl() ?? url('/cms');
        $this->fullDashboardUrl = Dashboard::getUrl();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::isAdminPm();
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.admin_overview.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.admin_overview.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.admin_overview.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.admin_overview.subheading');
    }
}
