<?php

namespace App\Filament\Ops\Clusters;

use Filament\Pages\SubNavigationPosition;

use Filament\Clusters\Cluster;

class FinanceCluster extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.finance');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.finance');
    }
}
