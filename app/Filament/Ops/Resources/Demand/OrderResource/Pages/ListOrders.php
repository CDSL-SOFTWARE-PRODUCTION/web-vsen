<?php

namespace App\Filament\Ops\Resources\Demand\OrderResource\Pages;

use App\Filament\Ops\Pages\DemandWorkspace;
use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Demand\OrderResource;
use App\Filament\Ops\Resources\Base\HasDemandFlowSubheading;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListOrders extends ListRecords
{
    use HasDemandFlowSubheading;

    protected static string $resource = OrderResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->demandFlowSubheading(__('ops.flow.step_1_chip'), __('ops.order.flow_hint'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('goDemandWorkspace')
                ->label(__('ops.order.actions.go_demand_workspace'))
                ->icon('heroicon-o-map')
                ->url(DemandWorkspace::getUrl()),
            Actions\Action::make('goContracts')
                ->label(__('ops.order.actions.go_contracts'))
                ->icon('heroicon-o-rectangle-stack')
                ->url(ContractResource::getUrl('index')),
            Actions\CreateAction::make(),
        ];
    }
}

