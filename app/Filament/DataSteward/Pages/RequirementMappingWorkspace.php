<?php

namespace App\Filament\DataSteward\Pages;

use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource;
use App\Filament\Ops\Clusters\MasterData\Resources\RequirementResource;
use App\Models\Knowledge\CanonicalProduct;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RequirementMappingWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.data-steward.pages.requirement-mapping-workspace';

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::canAccessDataStewardPanel();
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.data_steward.requirement_mapping.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.data_steward.requirement_mapping.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.data_steward.requirement_mapping.title');
    }

    /**
     * @return array{
     *   rows: list<array{id:int,sku:string,name:string,edit_url:string}>,
     *   missingCount:int,
     *   requirementsUrl:string
     * }
     */
    protected function getViewData(): array
    {
        $rows = CanonicalProduct::query()
            ->whereDoesntHave('requirements')
            ->orderBy('sku')
            ->limit(50)
            ->get(['id', 'sku', 'raw_name'])
            ->map(fn (CanonicalProduct $product): array => [
                'id' => (int) $product->id,
                'sku' => (string) $product->sku,
                'name' => (string) $product->raw_name,
                'edit_url' => CanonicalProductResource::getUrl('edit', ['record' => $product->id]),
            ])
            ->all();

        return [
            'rows' => $rows,
            'missingCount' => CanonicalProduct::query()->whereDoesntHave('requirements')->count(),
            'requirementsUrl' => RequirementResource::getUrl('index'),
        ];
    }
}
