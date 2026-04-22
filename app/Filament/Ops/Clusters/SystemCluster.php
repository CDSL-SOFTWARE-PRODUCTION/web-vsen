<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class SystemCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('ops.nav_groups.system');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.nav_groups.system');
    }
}
