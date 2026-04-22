<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class DeliveryCluster extends Cluster
{
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
