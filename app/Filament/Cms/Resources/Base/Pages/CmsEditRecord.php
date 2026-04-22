<?php

namespace App\Filament\Cms\Resources\Base\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

abstract class CmsEditRecord extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
