<?php

namespace App\Filament\Ops\Pages;

use App\Support\Ops\FilamentAccess;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class MasterDataHome extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.ops.pages.master-data-home';

    protected static ?int $navigationSort = -100;

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::isMasterDataSteward();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.master_data');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.master_data_home.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.master_data_home.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.master_data_home.subheading');
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.master_data_home.navigation');
    }
}
