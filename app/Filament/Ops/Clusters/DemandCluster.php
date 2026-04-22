<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class DemandCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.demand');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.demand');
    }
}
