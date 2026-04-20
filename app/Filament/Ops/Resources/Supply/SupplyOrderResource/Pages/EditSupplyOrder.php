<?php

namespace App\Filament\Ops\Resources\Supply\SupplyOrderResource\Pages;

use App\Filament\Ops\Resources\Supply\SupplyOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplyOrder extends EditRecord
{
    protected static string $resource = SupplyOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
