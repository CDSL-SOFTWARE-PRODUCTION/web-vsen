<?php

namespace App\Filament\Ops\Resources\Base\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

abstract class OpsListRecords extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
