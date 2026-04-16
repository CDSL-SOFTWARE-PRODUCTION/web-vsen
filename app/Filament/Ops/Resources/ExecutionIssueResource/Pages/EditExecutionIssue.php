<?php

namespace App\Filament\Ops\Resources\ExecutionIssueResource\Pages;

use App\Filament\Ops\Resources\ExecutionIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExecutionIssue extends EditRecord
{
    protected static string $resource = ExecutionIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
