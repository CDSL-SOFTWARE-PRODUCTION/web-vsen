<?php

namespace App\Filament\Ops\Resources;

use App\Domain\Knowledge\GenerateSkuFromFacetsService;
use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\CanonicalProductResource\Pages;
use App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers\LinkedPriceListItemsRelationManager;
use App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers\ProductAliasesRelationManager;
use App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers\ProductDocumentsRelationManager;
use App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers\RequirementsRelationManager;
use App\Models\Knowledge\CanonicalProduct;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CanonicalProductResource extends Resource
{
    protected static ?string $model = CanonicalProduct::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'sku';

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
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
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
                                        ->maxLength(64)
                                        ->label(__('ops.resources.canonical_product.fields.sku'))
                                        ->helperText(__('ops.resources.canonical_product.sku_helper')),
                                    Forms\Components\TextInput::make('raw_name')
                                        ->required()
                                        ->maxLength(512)
                                        ->label(__('ops.resources.canonical_product.fields.raw_name')),
                                    Forms\Components\Select::make('abc_class')
                                        ->label(__('ops.resources.canonical_product.fields.abc_class'))
                                        ->options([
                                            'A' => 'A',
                                            'B' => 'B',
                                            'C' => 'C',
                                        ])
                                        ->nullable(),
                                ]),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('ops.resources.canonical_product.tab_facets'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Forms\Components\KeyValue::make('spec_json')
                                ->label(__('ops.resources.canonical_product.spec_json_label'))
                                ->helperText(__('ops.resources.canonical_product.spec_json_helper'))
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
                            Forms\Components\TextInput::make('image_url')
                                ->label(__('ops.resources.canonical_product.fields.image_url'))
                                ->helperText(__('ops.resources.canonical_product.image_url_helper'))
                                ->maxLength(2048)
                                ->url()
                                ->live(onBlur: true)
                                ->columnSpanFull(),
                            Forms\Components\Placeholder::make('image_preview')
                                ->content(function (Forms\Get $get): HtmlString|string {
                                    $url = $get('image_url');
                                    if (! is_string($url) || $url === '') {
                                        return '';
                                    }

                                    return new HtmlString(
                                        '<img src="'.e($url).'" alt="" class="max-h-48 max-w-full rounded-lg border border-gray-200 object-contain dark:border-gray-700" loading="lazy" referrerpolicy="no-referrer" />'
                                    );
                                })
                                ->visible(fn (Forms\Get $get): bool => filled($get('image_url')))
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
                Tables\Columns\TextColumn::make('abc_class')
                    ->label(__('ops.resources.canonical_product.fields.abc_class'))
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('abc_class')
                    ->label(__('ops.resources.canonical_product.filters.abc_class'))
                    ->options(['A' => 'A', 'B' => 'B', 'C' => 'C']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make(
                __('ops.resources.canonical_product.relation_group_catalog'),
                [
                    ProductAliasesRelationManager::class,
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
