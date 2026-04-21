<?php

namespace App\Filament\Ops\Resources\System\FounderWorkCardResource\Pages;

use App\Filament\Ops\Resources\System\FounderWorkCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFounderWorkCards extends ListRecords
{
    protected static string $resource = FounderWorkCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
