<?php

namespace App\Filament\Ops\Resources\MasterData\PriceListResource\Pages;

use App\Filament\Ops\Resources\MasterData\PriceListResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreatePriceList extends CreateRecord
{
    protected static string $resource = PriceListResource::class;

    /**
     * After saving the header row, land on edit so the user can open the «Reference price lines» relation tab.
     */
    protected function getRedirectUrl(): string
    {
        return PriceListResource::getUrl('edit', ['record' => $this->record]);
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.resources.price_list.create_subheading');
    }
}
