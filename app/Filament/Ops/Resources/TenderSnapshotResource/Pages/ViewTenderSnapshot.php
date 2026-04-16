<?php

namespace App\Filament\Ops\Resources\TenderSnapshotResource\Pages;

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

                    Notification::make()
                        ->title('Snapshot locked')
                        ->success()
                        ->send();
                }),
        ];
    }
}

