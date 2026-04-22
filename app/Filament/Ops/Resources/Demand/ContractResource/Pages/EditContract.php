<?php

namespace App\Filament\Ops\Resources\Demand\ContractResource\Pages;

use App\Filament\Ops\Resources\Demand\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
