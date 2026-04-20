<?php

namespace App\Filament\Ops\Resources\Delivery\DeliveryRouteResource\Pages;

use App\Filament\Ops\Resources\Delivery\DeliveryRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryRoute extends EditRecord
{
    protected static string $resource = DeliveryRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
