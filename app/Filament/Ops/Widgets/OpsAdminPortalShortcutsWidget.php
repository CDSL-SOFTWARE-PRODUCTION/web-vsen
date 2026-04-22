<?php

namespace App\Filament\Ops\Widgets;

use App\Support\Ops\FilamentAccess;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Admin_PM-only shortcuts to non-Ops panels (master data, CMS), shown above the KPI dashboard.
 */
class OpsAdminPortalShortcutsWidget extends Widget
{
    protected static string $view = 'filament.ops.widgets.ops-admin-portal-shortcuts-widget';

    protected static ?int $sort = -100;

    protected int|string|array $columnSpan = 'full';

    public string $dataStewardUrl = '';

    public string $cmsUrl = '';

    public static function canView(): bool
    {
        return FilamentAccess::isAdminPm();
    }

    public function mount(): void
    {
        // Panel::getUrl() can be relative to the current request path; from /ops that breaks cross-panel links.
        $this->dataStewardUrl = $this->absolutePanelRootUrl('data-steward');
        $this->cmsUrl = $this->absolutePanelRootUrl('cms');
    }

    private function absolutePanelRootUrl(string $panelId): string
    {
        $path = trim(Filament::getPanel($panelId)->getPath(), '/');

        return url('/'.$path);
    }
}
