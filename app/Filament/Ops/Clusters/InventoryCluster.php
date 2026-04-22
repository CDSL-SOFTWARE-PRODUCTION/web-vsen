<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class InventoryCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.inventory');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.inventory');
    }
}
