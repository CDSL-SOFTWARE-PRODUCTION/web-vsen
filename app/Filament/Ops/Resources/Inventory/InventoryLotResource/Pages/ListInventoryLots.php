<?php

namespace App\Filament\Ops\Resources\Inventory\InventoryLotResource\Pages;

use App\Filament\Ops\Resources\Inventory\InventoryLotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryLots extends ListRecords
{
    protected static string $resource = InventoryLotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
