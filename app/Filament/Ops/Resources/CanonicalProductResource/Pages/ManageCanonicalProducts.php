<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\Pages;

use App\Filament\Ops\Resources\CanonicalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCanonicalProducts extends ManageRecords
{
    protected static string $resource = CanonicalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
