<?php

namespace App\Filament\Ops\Resources\Finance\InvoiceResource\Pages;

use App\Domain\Finance\CancelAndReissueInvoiceService;
use App\Filament\Ops\Resources\Finance\InvoiceResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelAndReissue')
                ->label(__('ops.invoice.actions.cancel_reissue'))
                ->visible(fn (): bool => $this->record->status === 'Issued')
                ->form([
                    TextInput::make('new_total')
                        ->label(__('ops.invoice.fields.new_total'))
                        ->numeric()
                        ->required()
                        ->default(fn (): string|float => $this->record->total_amount),
                ])
                ->action(function (array $data): void {
                    app(CancelAndReissueInvoiceService::class)->handle(
                        (int) $this->record->id,
                        (float) $data['new_total'],
                        auth()->id()
                    );
                    Notification::make()->title(__('ops.invoice.notifications.reissued'))->success()->send();
                    $this->redirect(InvoiceResource::getUrl('index'));
                }),
        ];
    }
}
