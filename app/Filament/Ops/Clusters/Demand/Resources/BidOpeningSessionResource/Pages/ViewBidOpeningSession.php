<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\BidOpeningSessionResource\Pages;


use App\Filament\Ops\Clusters\Demand\Resources\BidOpeningSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBidOpeningSession extends ViewRecord
{

    protected static string $resource = BidOpeningSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
