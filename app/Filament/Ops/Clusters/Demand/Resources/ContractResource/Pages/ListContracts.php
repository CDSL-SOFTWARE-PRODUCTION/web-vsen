<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\ContractResource\Pages;

use App\Filament\Ops\Clusters\Demand\Pages\DemandWorkspace;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource;
use App\Filament\Ops\Clusters\Demand\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('goDemandWorkspace')
                ->label(__('ops.contract.actions.go_demand_workspace'))
                ->icon('heroicon-o-map')
                ->url(DemandWorkspace::getUrl()),
            Actions\Action::make('goOrders')
                ->label(__('ops.contract.actions.go_orders'))
                ->icon('heroicon-o-clipboard-document-list')
                ->url(OrderResource::getUrl('index')),
            Actions\CreateAction::make(),
        ];
    }
}
