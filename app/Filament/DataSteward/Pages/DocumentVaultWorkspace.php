<?php

namespace App\Filament\DataSteward\Pages;

use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
use App\Filament\Ops\Resources\MasterData\MedicalDeviceDeclarationResource;
use App\Models\Knowledge\CanonicalProductDocument;
use App\Models\Knowledge\MedicalDeviceDeclarationDocument;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class DocumentVaultWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.data-steward.pages.document-vault-workspace';

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::canAccessDataStewardPanel();
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.data_steward.document_vault.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.data_steward.document_vault.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.data_steward.document_vault.title');
    }

    /**
     * @return array{
     *   declarationDocs:list<array<string,string|int|null>>,
     *   productDocs:list<array<string,string|int|null>>
     * }
     */
    protected function getViewData(): array
    {
        $declarationDocs = MedicalDeviceDeclarationDocument::query()
            ->with('medicalDeviceDeclaration:id,declaration_number')
            ->orderBy('expiry_date')
            ->limit(40)
            ->get()
            ->map(function (MedicalDeviceDeclarationDocument $doc): array {
                return [
                    'target' => (string) ($doc->medicalDeviceDeclaration?->declaration_number ?? '-'),
                    'type' => (string) $doc->document_type,
                    'status' => (string) $doc->status,
                    'expiry_date' => $doc->expiry_date?->format('d/m/Y'),
                    'edit_url' => $doc->medicalDeviceDeclaration
                        ? MedicalDeviceDeclarationResource::getUrl('edit', ['record' => $doc->medicalDeviceDeclaration->id])
                        : '',
                ];
            })
            ->all();

        $productDocs = CanonicalProductDocument::query()
            ->with('canonicalProduct:id,sku')
            ->orderBy('expiry_date')
            ->limit(40)
            ->get()
            ->map(function (CanonicalProductDocument $doc): array {
                return [
                    'target' => (string) ($doc->canonicalProduct?->sku ?? '-'),
                    'type' => (string) $doc->document_type,
                    'status' => (string) $doc->status,
                    'expiry_date' => $doc->expiry_date?->format('d/m/Y'),
                    'edit_url' => $doc->canonicalProduct
                        ? CanonicalProductResource::getUrl('edit', ['record' => $doc->canonicalProduct->id])
                        : '',
                ];
            })
            ->all();

        return [
            'declarationDocs' => $declarationDocs,
            'productDocs' => $productDocs,
        ];
    }
}
