<?php

namespace App\Filament\Ops\Resources\Demand\BidOpeningSessionResource\Pages;

use App\Filament\Ops\Resources\Demand\BidOpeningSessionResource;
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
