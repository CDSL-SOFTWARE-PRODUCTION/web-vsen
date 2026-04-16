<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class Supply extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.supply');
    }
}
