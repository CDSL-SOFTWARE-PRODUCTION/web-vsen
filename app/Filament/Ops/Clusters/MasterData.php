<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class MasterData extends Cluster
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.master_data');
    }
}
