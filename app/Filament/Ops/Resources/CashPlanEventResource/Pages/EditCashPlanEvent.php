<?php

namespace App\Filament\Ops\Resources\CashPlanEventResource\Pages;

use App\Filament\Ops\Resources\CashPlanEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashPlanEvent extends EditRecord
{
    protected static string $resource = CashPlanEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
