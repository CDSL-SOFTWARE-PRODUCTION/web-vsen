<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class SupplyCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.supply');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.supply');
    }
}
