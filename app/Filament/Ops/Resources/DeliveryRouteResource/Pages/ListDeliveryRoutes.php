<?php

namespace App\Filament\Ops\Resources\DeliveryRouteResource\Pages;

use App\Filament\Ops\Resources\DeliveryRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryRoutes extends ListRecords
{
    protected static string $resource = DeliveryRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
