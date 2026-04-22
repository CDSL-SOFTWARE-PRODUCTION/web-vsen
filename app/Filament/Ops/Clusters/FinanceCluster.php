<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class FinanceCluster extends Cluster
{
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
