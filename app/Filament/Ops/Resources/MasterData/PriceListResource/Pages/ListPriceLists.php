<?php

namespace App\Filament\Ops\Resources\MasterData\PriceListResource\Pages;

use App\Filament\Ops\Resources\MasterData\PriceListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriceLists extends ListRecords
{
    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
