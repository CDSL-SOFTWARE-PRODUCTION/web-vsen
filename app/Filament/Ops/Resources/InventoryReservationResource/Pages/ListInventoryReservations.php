<?php

namespace App\Filament\Ops\Resources\InventoryReservationResource\Pages;

use App\Filament\Ops\Resources\InventoryReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryReservations extends ListRecords
{
    protected static string $resource = InventoryReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
