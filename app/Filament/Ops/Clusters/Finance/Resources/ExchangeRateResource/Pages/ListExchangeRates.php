<?php

namespace App\Filament\Ops\Clusters\Finance\Resources\ExchangeRateResource\Pages;

use App\Filament\Ops\Clusters\Finance\Resources\ExchangeRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExchangeRates extends ListRecords
{
    protected static string $resource = ExchangeRateResource::class;

    public function getSubheading(): ?string
    {
        return __('ops.resources.exchange_rate.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
