<?php

namespace App\Filament\Ops\Resources\Demand\TenderSnapshotResource\Pages;

use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Filament\Ops\Resources\Demand\TenderSnapshotResource;
use App\Models\Demand\TenderSnapshot;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTenderSnapshot extends EditRecord
{
    protected static string $resource = TenderSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        /** @var TenderSnapshot $record */
        $record = $this->record;

        return [
            Actions\Action::make('lock')
                ->label(__('ops.tender_snapshot.actions.lock_snapshot'))
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
                        ->title(__('ops.tender_snapshot.notifications.locked'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('generatePlan')
                ->label(__('ops.tender_snapshot.actions.generate_execution_plan'))
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $record->isLocked())
                ->action(function () use ($record): void {
                    $existingContractId = $record->contracts()->value('id');
                    $contract = app(GenerateExecutionPlanService::class)->handle($record->id, auth()->id());

                    Notification::make()
                        ->title($existingContractId === null
                            ? __('ops.tender_snapshot.notifications.plan_created', ['id' => $contract->id])
                            : __('ops.tender_snapshot.notifications.plan_exists', ['id' => $contract->id]))
                        ->color($existingContractId === null ? 'success' : 'warning')
                        ->send();
                }),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !$record->isLocked()),
        ];
    }
}

