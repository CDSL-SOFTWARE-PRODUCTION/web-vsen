<?php

namespace App\Filament\Ops\Pages;

use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Demand\OrderResource;
use App\Filament\Ops\Resources\Demand\TenderSnapshotResource;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class DemandWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static string $view = 'filament.ops.pages.demand-workspace';

    protected static ?int $navigationSort = -100;

    public static function shouldRegisterNavigation(): bool
    {
        if (FilamentAccess::isMasterDataSteward()) {
            return false;
        }

        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.demand');
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.demand_workspace.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.demand_workspace.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.demand_workspace.title');
    }

    /**
     * @return array{
     *   primaryFlow:list<array{title:string,description:string,action:string,url:string,info:?string}>,
     *   quickActions:list<array{title:string,description:string,action:string,url:string,info:?string}>
     * }
     */
    protected function getViewData(): array
    {
        $isManagerView = FilamentAccess::allowRoles(['Admin_PM', 'KeToan']);

        $primaryFlow = [
            [
                'title' => __('ops.demand_workspace.cards.orders.title'),
                'description' => __('ops.demand_workspace.cards.orders.description'),
                'action' => __('ops.demand_workspace.cards.orders.action'),
                'url' => OrderResource::getUrl('index'),
                'info' => __('ops.demand_workspace.cards.orders.info_tooltip'),
            ],
            [
                'title' => __('ops.demand_workspace.cards.contracts.title'),
                'description' => __('ops.demand_workspace.cards.contracts.description'),
                'action' => __('ops.demand_workspace.cards.contracts.action'),
                'url' => ContractResource::getUrl('index'),
                'info' => null,
            ],
        ];

        $quickActions = [
            [
                'title' => __('ops.demand_workspace.cards.execution_plan.title'),
                'description' => __('ops.demand_workspace.cards.execution_plan.description'),
                'action' => __('ops.demand_workspace.cards.execution_plan.action'),
                'url' => TenderSnapshotResource::getUrl('index'),
                'info' => null,
            ],
            [
                'title' => __('ops.demand_workspace.cards.gate_pipeline.title'),
                'description' => $isManagerView
                    ? __('ops.demand_workspace.cards.gate_pipeline.description_manager')
                    : __('ops.demand_workspace.cards.gate_pipeline.description_ops'),
                'action' => __('ops.demand_workspace.cards.gate_pipeline.action'),
                'url' => GatePipeline::getUrl(),
                'info' => null,
            ],
        ];

        return [
            'primaryFlow' => $primaryFlow,
            'quickActions' => $quickActions,
        ];
    }
}
