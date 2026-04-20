<?php

namespace App\Filament\Ops\Resources\MasterData\PartnerResource\Pages;

use App\Filament\Ops\Resources\MasterData\PartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
