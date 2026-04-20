<?php

namespace App\Filament\Ops\Resources\ProductFamilyResource\Pages;

use App\Filament\Ops\Resources\ProductFamilyResource;
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
