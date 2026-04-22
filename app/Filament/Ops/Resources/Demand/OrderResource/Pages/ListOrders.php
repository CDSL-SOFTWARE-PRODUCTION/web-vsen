<?php

namespace App\Filament\Ops\Resources\Demand\OrderResource\Pages;

use App\Filament\Ops\Pages\DemandWorkspace;
use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Demand\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

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

