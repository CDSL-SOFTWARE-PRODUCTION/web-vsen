<?php

namespace App\Filament\Ops\Clusters;

use Filament\Clusters\Cluster;

class Demand extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Demand & Contract';
    protected static ?int $navigationSort = 1;
}
