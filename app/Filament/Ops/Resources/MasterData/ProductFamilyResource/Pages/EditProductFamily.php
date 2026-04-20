<?php

namespace App\Filament\Ops\Resources\MasterData\ProductFamilyResource\Pages;

use App\Filament\Ops\Resources\MasterData\ProductFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductFamily extends EditRecord
{
    protected static string $resource = ProductFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
