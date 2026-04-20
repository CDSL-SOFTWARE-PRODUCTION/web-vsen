<?php

namespace App\Filament\Ops\Resources\Delivery\VehicleResource\Pages;

use App\Filament\Ops\Resources\Delivery\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
