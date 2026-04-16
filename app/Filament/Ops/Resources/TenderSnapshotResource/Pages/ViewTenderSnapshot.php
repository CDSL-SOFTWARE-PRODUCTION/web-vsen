<?php

namespace App\Filament\Ops\Resources\TenderSnapshotResource\Pages;

use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Filament\Ops\Resources\TenderSnapshotResource;
use App\Models\Demand\TenderSnapshot;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTenderSnapshot extends ViewRecord
{
    protected static string $resource = TenderSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        /** @var TenderSnapshot $record */
        $record = $this->record;

        return [
            Actions\Action::make('lock')
                ->label('Lock Snapshot')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => !$record->isLocked())
                ->action(function () use ($record): void {
                    $record->lock(auth()->id());
                    app(AuditLogService::class)->log(
                        auth()->id(),
                        'TenderSnapshot',
                        $record->id,
                        'LockTenderSnapshot',
                        ['snapshot_hash' => $record->snapshot_hash]
                    );

                    Notification::make()
                        ->title('Snapshot locked')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('generatePlan')
                ->label('Generate Execution Plan')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $record->isLocked())
                ->action(function () use ($record): void {
                    $existingContractId = $record->contracts()->value('id');
                    $contract = app(GenerateExecutionPlanService::class)->handle($record->id, auth()->id());

                    Notification::make()
                        ->title($existingContractId === null
                            ? "Execution plan generated (Contract #{$contract->id})"
                            : "Execution plan already exists (Contract #{$contract->id})")
                        ->color($existingContractId === null ? 'success' : 'warning')
                        ->send();
                }),
        ];
    }
}

