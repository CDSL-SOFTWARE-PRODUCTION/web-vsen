<?php

namespace App\Filament\Ops\Resources\Base\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

abstract class OpsEditRecord extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
