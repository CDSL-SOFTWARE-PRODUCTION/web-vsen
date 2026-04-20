<?php

namespace App\Filament\Ops\Resources\MedicalDeviceDeclarationResource\Pages;

use App\Filament\Ops\Resources\MedicalDeviceDeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalDeviceDeclarations extends ListRecords
{
    protected static string $resource = MedicalDeviceDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
