<?php

namespace App\Filament\Ops\Resources\Demand\OrderResource\Pages;

use App\Domain\Demand\CloseContractCommandService;
use App\Domain\Demand\ConfirmContractCommandService;
use App\Domain\Demand\ConfirmFulfillmentCommandService;
use App\Domain\Demand\StartExecutionCommandService;
use App\Filament\Ops\Resources\Demand\OrderResource;
use App\Models\Demand\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('nextRecommended')
                ->label(fn (Order $record): string => $this->nextActionLabel($record))
                ->icon(fn (Order $record): string => $this->nextActionIcon($record))
                ->color(fn (Order $record): string => $this->nextActionColor($record))
                ->visible(fn (Order $record): bool => $this->hasNextTransition($record))
                ->requiresConfirmation()
                ->action(function (Order $record): void {
                    $result = $this->runNextTransition($record);
                    $record->refresh();

                    Notification::make()
                        ->title($result['title'])
                        ->body(
                            $result['warningRaised']
                                ? implode("\n", $result['warnings'])
                                : __('ops.order.notifications.transition_ok')
                        )
                        ->color($result['warningRaised'] ? 'warning' : 'success')
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }

    private function hasNextTransition(Order $record): bool
    {
        return match ($record->state) {
            'AwardTender', 'ConfirmContract', 'StartExecution', 'Fulfilled' => true,
            default => false,
        };
    }

    private function nextActionLabel(Order $record): string
    {
        return match ($record->state) {
            'AwardTender' => __('ops.order.actions.confirm_contract'),
            'ConfirmContract' => __('ops.order.actions.start_execution'),
            'StartExecution' => __('ops.order.actions.confirm_fulfillment'),
            'Fulfilled' => __('ops.order.actions.close_contract'),
            default => __('ops.order.actions.start_execution'),
        };
    }

    private function nextActionIcon(Order $record): string
    {
        return match ($record->state) {
            'AwardTender' => 'heroicon-o-check-badge',
            'ConfirmContract' => 'heroicon-o-play',
            'StartExecution' => 'heroicon-o-check-circle',
            'Fulfilled' => 'heroicon-o-lock-closed',
            default => 'heroicon-o-arrow-right-circle',
        };
    }

    private function nextActionColor(Order $record): string
    {
        return match ($record->state) {
            'AwardTender', 'StartExecution' => 'success',
            'ConfirmContract' => 'primary',
            'Fulfilled' => 'gray',
            default => 'primary',
        };
    }

    /**
     * @return array{title:string, warningRaised:bool, warnings:list<string>}
     */
    private function runNextTransition(Order $record): array
    {
        $result = match ($record->state) {
            'AwardTender' => app(ConfirmContractCommandService::class)->handle($record->id, auth()->id()),
            'ConfirmContract' => app(StartExecutionCommandService::class)->handle($record->id, auth()->id()),
            'StartExecution' => app(ConfirmFulfillmentCommandService::class)->handle($record->id, auth()->id()),
            'Fulfilled' => app(CloseContractCommandService::class)->handle($record->id, auth()->id()),
            default => null,
        };

        if ($result === null) {
            return [
                'title' => __('ops.order.notifications.transition_ok'),
                'warningRaised' => false,
                'warnings' => [],
            ];
        }

        $title = match ($record->state) {
            'AwardTender' => __('ops.order.notifications.moved_confirm_contract'),
            'ConfirmContract' => __('ops.order.notifications.moved_start_execution'),
            'StartExecution' => __('ops.order.notifications.moved_fulfilled'),
            'Fulfilled' => __('ops.order.notifications.moved_contract_closed'),
            default => __('ops.order.notifications.transition_ok'),
        };

        return [
            'title' => $title,
            'warningRaised' => $result->warningRaised,
            'warnings' => $result->warnings,
        ];
    }
}

