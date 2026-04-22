<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class DeliveryCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.delivery');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.delivery');
    }
}
