<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources;

use App\Filament\Ops\Clusters\MasterDataCluster;

use App\Domain\Knowledge\GenerateSkuFromFacetsService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource\Pages;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource\RelationManagers\LinkedPriceListItemsRelationManager;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource\RelationManagers\ProductAliasesRelationManager;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource\RelationManagers\ProductDocumentsRelationManager;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource\RelationManagers\RequirementsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\Knowledge\MedicalDeviceDeclaration;
use App\Models\Knowledge\ProductFamily;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class CanonicalProductResource extends OpsResource
{
    protected static ?string $model = CanonicalProduct::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'sku';

    

    protected static function visibleInMasterDataStewardSidebar(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.canonical_product.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.canonical_product.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.canonical_product.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL)
            || FilamentAccess::canAccessDataStewardPanel();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['medicalDeviceDeclaration', 'productFamily']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()
                ->contained(false)
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('ops.resources.canonical_product.tab_identity'))
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\Grid::make(['default' => 1, 'lg' => 3])
                                ->schema([
                                    Forms\Components\TextInput::make('sku')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(64)
                                        ->label(__('ops.resources.canonical_product.fields.sku')),
                                    Forms\Components\TextInput::make('raw_name')
                                        ->required()
                                        ->maxLength(512)
                                        ->label(__('ops.resources.canonical_product.fields.raw_name')),
                                    Forms\Components\Select::make('abc_class')
                                        ->label(__('ops.resources.canonical_product.fields.abc_class'))
                                        ->hintIcon('heroicon-m-information-circle')
                                        ->hintIconTooltip(__('ops.resources.canonical_product.abc_class_tooltip'))
                                        ->options([
                                            'A' => 'A',
                                            'B' => 'B',
                                            'C' => 'C',
                                        ])
                                        ->nullable(),
                                ]),
                            Forms\Components\Select::make('medical_device_declaration_id')
                                ->label(__('ops.resources.canonical_product.fields.medical_device_declaration'))
                                ->hintIcon('heroicon-m-information-circle')
                                ->hintIconTooltip(__('ops.resources.canonical_product.medical_device_declaration_tooltip'))
                                ->relationship(
                                    'medicalDeviceDeclaration',
                                    'declaration_number',
                                    fn ($query) => $query->orderBy('declaration_number')
                                )
                                ->getOptionLabelFromRecordUsing(fn (MedicalDeviceDeclaration $record): string => $record->declaration_number
                                    .($record->device_name_official ? ' — '.$record->device_name_official : ''))
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('product_family_id')
                                ->label(__('ops.resources.canonical_product.fields.product_family'))
                                ->relationship(
                                    'productFamily',
                                    'name',
                                    fn ($query) => $query->orderBy('name')
                                )
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->hintIcon('heroicon-m-information-circle')
                                ->hintIconTooltip(__('ops.resources.canonical_product.product_family_tooltip'))
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('ops.resources.canonical_product.tab_facets'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Forms\Components\KeyValue::make('spec_json')
                                ->label(__('ops.resources.canonical_product.spec_json_label'))
                                ->hintIcon('heroicon-m-information-circle')
                                ->hintIconTooltip(__('ops.resources.canonical_product.facets_sku_info_tooltip'))
                                ->keyLabel(__('ops.resources.canonical_product.spec_key'))
                                ->valueLabel(__('ops.resources.canonical_product.spec_value'))
                                ->addActionLabel(__('ops.resources.canonical_product.spec_add'))
                                ->columnSpanFull(),
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('generateSkuFromFacets')
                                    ->label(__('ops.resources.canonical_product.generate_sku_action'))
                                    ->icon('heroicon-m-sparkles')
                                    ->color('gray')
                                    ->action(function (Forms\Get $get, Forms\Set $set, $livewire): void {
                                        $facets = $get('spec_json');
                                        if (! is_array($facets)) {
                                            $facets = [];
                                        }
                                        $ignoreId = null;
                                        if ($livewire instanceof EditRecord && $livewire->getRecord()) {
                                            $ignoreId = (int) $livewire->getRecord()->getKey();
                                        }
                                        try {
                                            $sku = app(GenerateSkuFromFacetsService::class)->generate($facets, $ignoreId);
                                        } catch (\Throwable $e) {
                                            Notification::make()
                                                ->title($e->getMessage())
                                                ->danger()
                                                ->send();

                                            return;
                                        }
                                        $set('sku', $sku);
                                        Notification::make()
                                            ->title(__('ops.resources.canonical_product.generate_sku_success'))
                                            ->success()
                                            ->send();
                                    }),
                            ])->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('ops.resources.canonical_product.tab_media'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Forms\Components\Repeater::make('image_urls')
                                ->label(__('ops.resources.canonical_product.fields.image_urls'))
                                ->hintIcon('heroicon-m-information-circle', __('ops.resources.canonical_product.image_urls_helper'))
                                ->simple(
                                    Forms\Components\TextInput::make('')
                                        ->url()
                                        ->maxLength(2048)
                                        ->placeholder('https://')
                                )
                                ->defaultItems(0)
                                ->addActionLabel(__('ops.resources.canonical_product.image_urls_add'))
                                ->reorderable()
                                ->live()
                                ->columnSpanFull()
                                ->afterStateHydrated(function (Forms\Components\Repeater $component): void {
                                    $state = $component->getState();
                                    if (! is_array($state)) {
                                        $component->state([]);
                                    }
                                })
                                ->dehydrateStateUsing(function (?array $state): ?array {
                                    if (! is_array($state)) {
                                        return null;
                                    }
                                    $clean = array_values(array_filter(
                                        $state,
                                        fn (mixed $v): bool => is_string($v) && trim($v) !== ''
                                    ));

                                    return $clean === [] ? null : $clean;
                                }),
                            Forms\Components\Placeholder::make('image_urls_preview')
                                ->label(__('ops.resources.canonical_product.image_urls_preview'))
                                ->content(function (Forms\Get $get): HtmlString|string {
                                    $urls = $get('image_urls');
                                    if (! is_array($urls)) {
                                        return '';
                                    }
                                    $parts = [];
                                    foreach ($urls as $url) {
                                        if (! is_string($url) || trim($url) === '') {
                                            continue;
                                        }
                                        $parts[] = '<img src="'.e($url).'" alt="" class="max-h-40 max-w-full rounded-lg border border-gray-200 object-contain dark:border-gray-700" loading="lazy" referrerpolicy="no-referrer" />';
                                    }

                                    if ($parts === []) {
                                        return '';
                                    }

                                    return new HtmlString(
                                        '<div class="flex flex-wrap gap-3">'.implode('', $parts).'</div>'
                                    );
                                })
                                ->visible(function (Forms\Get $get): bool {
                                    $urls = $get('image_urls');

                                    return is_array($urls) && count(array_filter($urls, fn (mixed $u): bool => is_string($u) && trim($u) !== '')) > 0;
                                })
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('ops.resources.canonical_product.fields.sku'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('raw_name')
                    ->label(__('ops.resources.canonical_product.fields.raw_name'))
                    ->limit(40),
                Tables\Columns\TextColumn::make('medicalDeviceDeclaration.declaration_number')
                    ->label(__('ops.resources.canonical_product.fields.medical_device_declaration'))
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('productFamily.name')
                    ->label(__('ops.resources.canonical_product.fields.product_family'))
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('abc_class')
                    ->label(__('ops.resources.canonical_product.fields.abc_class'))
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('abc_class')
                    ->label(__('ops.resources.canonical_product.filters.abc_class'))
                    ->options(['A' => 'A', 'B' => 'B', 'C' => 'C']),
                Tables\Filters\SelectFilter::make('product_family_id')
                    ->label(__('ops.resources.canonical_product.fields.product_family'))
                    ->relationship('productFamily', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('assignMedicalDeviceDeclaration')
                    ->label(__('ops.resources.canonical_product.bulk_assign_declaration'))
                    ->icon('heroicon-o-link')
                    ->form([
                        Forms\Components\Select::make('medical_device_declaration_id')
                            ->label(__('ops.resources.canonical_product.fields.medical_device_declaration'))
                            ->relationship(
                                'medicalDeviceDeclaration',
                                'declaration_number',
                                fn ($query) => $query->orderBy('declaration_number')
                            )
                            ->getOptionLabelFromRecordUsing(fn (MedicalDeviceDeclaration $record): string => $record->declaration_number
                                .($record->device_name_official ? ' — '.$record->device_name_official : ''))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $id = $data['medical_device_declaration_id'] ?? null;
                        if ($id === null || $id === '') {
                            return;
                        }
                        $declarationId = (int) $id;
                        $records->each(fn (CanonicalProduct $product) => $product->update([
                            'medical_device_declaration_id' => $declarationId,
                        ]));
                        Notification::make()
                            ->title(__('ops.resources.canonical_product.bulk_assign_declaration_success', [
                                'count' => $records->count(),
                            ]))
                            ->success()
                            ->send();
                    }),
                BulkAction::make('assignProductFamily')
                    ->label(__('ops.resources.canonical_product.bulk_assign_family'))
                    ->icon('heroicon-o-squares-2x2')
                    ->form([
                        Forms\Components\Select::make('product_family_id')
                            ->label(__('ops.resources.canonical_product.fields.product_family'))
                            ->relationship(
                                'productFamily',
                                'name',
                                fn ($query) => $query->orderBy('name')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $id = $data['product_family_id'] ?? null;
                        if ($id === null || $id === '') {
                            return;
                        }
                        $familyId = (int) $id;
                        $records->each(fn (CanonicalProduct $product) => $product->update([
                            'product_family_id' => $familyId,
                        ]));
                        Notification::make()
                            ->title(__('ops.resources.canonical_product.bulk_assign_family_success', [
                                'count' => $records->count(),
                            ]))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make(
                __('ops.resources.canonical_product.relation_group_aliases'),
                [
                    ProductAliasesRelationManager::class,
                ],
            ),
            RelationGroup::make(
                __('ops.resources.canonical_product.relation_group_compliance'),
                [
                    RequirementsRelationManager::class,
                    ProductDocumentsRelationManager::class,
                ],
            ),
            LinkedPriceListItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCanonicalProducts::route('/'),
            'create' => Pages\CreateCanonicalProduct::route('/create'),
            'edit' => Pages\EditCanonicalProduct::route('/{record}/edit'),
        ];
    }
}
