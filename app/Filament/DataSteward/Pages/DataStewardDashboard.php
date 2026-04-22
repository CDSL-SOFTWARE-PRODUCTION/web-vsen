<?php

namespace App\Filament\DataSteward\Pages;

use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource;
use App\Filament\Ops\Clusters\MasterData\Resources\MedicalDeviceDeclarationResource;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\Knowledge\CanonicalProductDocument;
use App\Models\Knowledge\MedicalDeviceDeclaration;
use App\Models\Knowledge\MedicalDeviceDeclarationDocument;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class DataStewardDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -100;

    protected static string $view = 'filament.data-steward.pages.dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::canAccessDataStewardPanel();
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.data_steward.dashboard.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.data_steward.dashboard.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.data_steward.dashboard.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.data_steward.dashboard.subheading');
    }

    /**
     * @return array{
     *   triage: array<string,int>,
     *   shortcuts: list<array{title:string,description:string,action:string,url:string}>,
     *   metrics: list<string>
     * }
     */
    protected function getViewData(): array
    {
        $today = now()->startOfDay()->toDateString();
        $in30Days = now()->addDays(30)->endOfDay()->toDateString();

        $triage = [
            'declarations_without_documents' => MedicalDeviceDeclaration::query()
                ->whereDoesntHave('documents')
                ->count(),
            'products_without_declaration' => CanonicalProduct::query()
                ->whereNull('medical_device_declaration_id')
                ->count(),
            'products_without_requirements' => CanonicalProduct::query()
                ->whereDoesntHave('requirements')
                ->count(),
            'declaration_docs_expiring_30d' => MedicalDeviceDeclarationDocument::query()
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today, $in30Days])
                ->count(),
            'product_docs_expiring_30d' => CanonicalProductDocument::query()
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today, $in30Days])
                ->count(),
        ];

        $shortcuts = [
            [
                'title' => __('ops.data_steward.shortcuts.declarations.title'),
                'description' => __('ops.data_steward.shortcuts.declarations.description'),
                'action' => __('ops.data_steward.shortcuts.declarations.action'),
                'url' => MedicalDeviceDeclarationResource::getUrl('index'),
            ],
            [
                'title' => __('ops.data_steward.shortcuts.products.title'),
                'description' => __('ops.data_steward.shortcuts.products.description'),
                'action' => __('ops.data_steward.shortcuts.products.action'),
                'url' => CanonicalProductResource::getUrl('index'),
            ],
            [
                'title' => __('ops.data_steward.shortcuts.requirement_mapping.title'),
                'description' => __('ops.data_steward.shortcuts.requirement_mapping.description'),
                'action' => __('ops.data_steward.shortcuts.requirement_mapping.action'),
                'url' => RequirementMappingWorkspace::getUrl(),
            ],
            [
                'title' => __('ops.data_steward.shortcuts.document_vault.title'),
                'description' => __('ops.data_steward.shortcuts.document_vault.description'),
                'action' => __('ops.data_steward.shortcuts.document_vault.action'),
                'url' => DocumentVaultWorkspace::getUrl(),
            ],
        ];

        return [
            'triage' => $triage,
            'shortcuts' => $shortcuts,
            'metrics' => [
                __('ops.data_steward.release_metrics.time_to_complete'),
                __('ops.data_steward.release_metrics.completion_rate'),
                __('ops.data_steward.release_metrics.confusion_points'),
            ],
        ];
    }
}
