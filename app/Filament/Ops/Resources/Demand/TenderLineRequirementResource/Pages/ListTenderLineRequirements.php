<?php

namespace App\Filament\Ops\Resources\Demand\TenderLineRequirementResource\Pages;

use App\Filament\Ops\Resources\Demand\TenderLineRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenderLineRequirements extends ListRecords
{
    protected static string $resource = TenderLineRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
