<?php

namespace App\Filament\Ops\Resources\Demand\DocumentResource\Pages;

use App\Filament\Ops\Resources\Demand\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
