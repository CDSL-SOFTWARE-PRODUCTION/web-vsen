<?php

namespace App\Filament\Ops\Resources\LegalEntityResource\Pages;

use App\Filament\Ops\Resources\LegalEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalEntities extends ListRecords
{
    protected static string $resource = LegalEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
