<?php

namespace App\Filament\Ops\Resources\Demand\ExecutionIssueResource\Pages;

use App\Filament\Ops\Resources\Demand\ExecutionIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExecutionIssues extends ListRecords
{
    protected static string $resource = ExecutionIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
