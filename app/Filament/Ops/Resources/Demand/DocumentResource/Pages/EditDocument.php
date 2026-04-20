<?php

namespace App\Filament\Ops\Resources\Demand\DocumentResource\Pages;

use App\Filament\Ops\Resources\Demand\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
