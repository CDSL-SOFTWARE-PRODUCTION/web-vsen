<?php

namespace App\Filament\Ops\Clusters\Demand\Pages;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\DemandCluster;

use App\Domain\Execution\GateEvaluator;
use App\Filament\Ops\Clusters\Demand\Pages\DemandWorkspace;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource;
use App\Models\Ops\Contract;
use App\Support\Ops\FilamentAccess;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class GatePipeline extends Page
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = DemandCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string $view = 'filament.ops.pages.gate-pipeline';

    protected static ?int $navigationSort = -90;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    

    public static function getNavigationLabel(): string
    {
        return __('ops.gate_pipeline.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.gate_pipeline.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.gate_pipeline.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.gate_pipeline.subheading');
    }

    protected function getHeaderActions(): array
    {
        $isCompact = $this->isCompactMode();

        return [
            Action::make('goDemandWorkspace')
                ->label(__('ops.gate_pipeline.actions.go_demand_workspace'))
                ->icon('heroicon-o-map')
                ->color('gray')
                ->url(DemandWorkspace::getUrl()),
            Action::make('goContracts')
                ->label(__('ops.gate_pipeline.actions.go_contracts'))
                ->icon('heroicon-o-rectangle-stack')
                ->url(ContractResource::getUrl('index')),
            Action::make('toggleDensity')
                ->label($isCompact ? __('ops.gate_pipeline.actions.switch_verbose') : __('ops.gate_pipeline.actions.switch_compact'))
                ->icon($isCompact ? 'heroicon-o-arrows-pointing-out' : 'heroicon-o-arrows-pointing-in')
                ->color('gray')
                ->url($this->buildDensityUrl($isCompact ? 'verbose' : 'compact')),
        ];
    }

    /**
     * @return array{
     *   stages: array{
     *     pre_activate: array{label:string,count:int,cards:list<array<string,mixed>>},
     *     pre_delivery: array{label:string,count:int,cards:list<array<string,mixed>>},
     *     pre_payment: array{label:string,count:int,cards:list<array<string,mixed>>}
     *   },
     *   totalContracts:int,
     *   isCompactMode:bool
     * }
     */
    protected function getViewData(): array
    {
        $gateEvaluator = app(GateEvaluator::class);
        $isCompactMode = $this->isCompactMode();
        $contracts = Contract::query()
            ->orderByRaw('CASE WHEN next_delivery_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('next_delivery_due_date')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        $stages = [
            'pre_activate' => [
                'label' => __('ops.gate_pipeline.stage.pre_activate'),
                'count' => 0,
                'cards' => [],
            ],
            'pre_delivery' => [
                'label' => __('ops.gate_pipeline.stage.pre_delivery'),
                'count' => 0,
                'cards' => [],
            ],
            'pre_payment' => [
                'label' => __('ops.gate_pipeline.stage.pre_payment'),
                'count' => 0,
                'cards' => [],
            ],
        ];

        foreach ($contracts as $contract) {
            $preActivate = $gateEvaluator->evaluatePreActivate($contract);
            $preDelivery = $gateEvaluator->evaluatePreDelivery($contract);
            $prePayment = $gateEvaluator->evaluatePrePayment($contract);

            $activeStage = 'pre_payment';
            $activeResult = $prePayment;
            if (($preActivate['hasWarnings'] ?? false) === true) {
                $activeStage = 'pre_activate';
                $activeResult = $preActivate;
            } elseif (($preDelivery['hasWarnings'] ?? false) === true) {
                $activeStage = 'pre_delivery';
                $activeResult = $preDelivery;
            }

            $cards = &$stages[$activeStage]['cards'];
            $cards[] = [
                'id' => $contract->id,
                'contract_code' => $contract->contract_code,
                'name' => $contract->name,
                'customer_name' => $contract->customer_name,
                'risk_level' => $contract->risk_level,
                'next_delivery_due_date' => $contract->next_delivery_due_date?->format('d/m/Y'),
                'status' => (($activeResult['hasWarnings'] ?? false) === true) ? 'warning' : 'success',
                'warnings' => array_slice((array) ($activeResult['warnings'] ?? []), 0, 3),
                'warnings_count' => count((array) ($activeResult['warnings'] ?? [])),
                'url' => ContractResource::getUrl('edit', ['record' => $contract->id]),
                'gates' => [
                    'pre_activate' => (($preActivate['hasWarnings'] ?? false) === true) ? 'warning' : 'pass',
                    'pre_delivery' => (($preDelivery['hasWarnings'] ?? false) === true) ? 'warning' : 'pass',
                    'pre_payment' => (($prePayment['hasWarnings'] ?? false) === true) ? 'warning' : 'pass',
                ],
            ];
            unset($cards);
        }

        foreach (array_keys($stages) as $key) {
            $stages[$key]['count'] = count($stages[$key]['cards']);
        }

        return [
            'stages' => $stages,
            'totalContracts' => $contracts->count(),
            'isCompactMode' => $isCompactMode,
        ];
    }

    private function isCompactMode(): bool
    {
        $density = request()->query('density');
        if ($density === 'compact') {
            return true;
        }

        if ($density === 'verbose') {
            return false;
        }

        return FilamentAccess::allowRoles(['Admin_PM', 'KeToan']);
    }

    private function buildDensityUrl(string $density): string
    {
        $currentQuery = request()->query();
        $nextQuery = Arr::except($currentQuery, ['density']);
        $nextQuery['density'] = $density;

        return request()->url().'?'.http_build_query($nextQuery);
    }
}
