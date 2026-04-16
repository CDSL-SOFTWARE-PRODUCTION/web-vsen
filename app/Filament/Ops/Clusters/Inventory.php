<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class Inventory extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.inventory');
    }
}
