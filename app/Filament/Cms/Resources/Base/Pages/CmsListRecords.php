<?php

namespace App\Filament\Cms\Resources\Base\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

abstract class CmsListRecords extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
