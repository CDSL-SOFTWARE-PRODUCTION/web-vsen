<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class MasterDataCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.master_data');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.master_data');
    }
}
