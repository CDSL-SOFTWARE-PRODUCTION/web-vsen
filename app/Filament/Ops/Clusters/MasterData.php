<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class MasterData extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.master_data');
    }
}
