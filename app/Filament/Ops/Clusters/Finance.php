<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class Finance extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.finance');
    }
}
