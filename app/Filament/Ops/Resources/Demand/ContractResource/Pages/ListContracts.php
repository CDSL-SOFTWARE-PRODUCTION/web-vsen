<?php

namespace App\Filament\Ops\Resources\Demand\ContractResource\Pages;

use App\Filament\Ops\Pages\DemandWorkspace;
use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Demand\OrderResource;
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
