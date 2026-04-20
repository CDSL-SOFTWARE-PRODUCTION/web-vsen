<?php

namespace App\Filament\Ops\Resources\MasterData\CanonicalProductResource\Pages;

use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCanonicalProducts extends ListRecords
{
    protected static string $resource = CanonicalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
