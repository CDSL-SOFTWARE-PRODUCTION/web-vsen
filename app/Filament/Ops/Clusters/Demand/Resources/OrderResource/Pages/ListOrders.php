<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\OrderResource\Pages;


use App\Filament\Ops\Clusters\Demand\Pages\DemandWorkspace;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource;
use App\Filament\Ops\Clusters\Demand\Resources\OrderResource;
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

