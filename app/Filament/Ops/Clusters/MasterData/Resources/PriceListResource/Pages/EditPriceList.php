<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources\PriceListResource\Pages;


use App\Filament\Ops\Clusters\MasterData\Resources\PriceListResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPriceList extends EditRecord
{

    protected static string $resource = PriceListResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->getRecord()->items()->count() === 0) {
            return __('ops.resources.price_list.edit_subheading_empty_items');
        }

        return null;
    }
}
