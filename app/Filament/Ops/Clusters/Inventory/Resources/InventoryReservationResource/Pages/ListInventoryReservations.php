<?php

namespace App\Filament\Ops\Clusters\Inventory\Resources\InventoryReservationResource\Pages;

use App\Filament\Ops\Resources\Base\Pages\OpsListRecords;
use App\Filament\Ops\Clusters\Inventory\Resources\InventoryReservationResource;

class ListInventoryReservations extends OpsListRecords
{
    protected static string $resource = InventoryReservationResource::class;
}
