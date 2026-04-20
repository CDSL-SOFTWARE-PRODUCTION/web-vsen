<?php

namespace App\Filament\Ops\Resources\Demand\BidOpeningSessionResource\Pages;

use App\Filament\Ops\Resources\Demand\BidOpeningSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBidOpeningSession extends EditRecord
{
    protected static string $resource = BidOpeningSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
