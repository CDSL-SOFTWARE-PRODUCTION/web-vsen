<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class Demand extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
        protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('ops.clusters.demand');
    }
}
