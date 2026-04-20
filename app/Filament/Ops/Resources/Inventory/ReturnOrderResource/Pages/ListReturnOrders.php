<?php

namespace App\Filament\Ops\Resources\Inventory\ReturnOrderResource\Pages;

use App\Filament\Ops\Resources\Inventory\ReturnOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnOrders extends ListRecords
{
    protected static string $resource = ReturnOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
