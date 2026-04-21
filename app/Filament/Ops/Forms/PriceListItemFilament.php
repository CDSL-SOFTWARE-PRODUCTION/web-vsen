<?php

namespace App\Filament\Ops\Forms;

use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
use App\Filament\Ops\Resources\MasterData\PriceListResource;
use App\Models\Demand\PriceList;
use App\Models\Demand\PriceListItem;
use App\Support\Currency\CurrencyConverter;
use App\Support\Currency\CurrencyFormatter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared Filament form + table for {@see PriceListItem} (relation manager + procurement SSOT page).
 */
final class PriceListItemFilament
{
    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function itemFormComponents(PriceList $owner): array
    {
        return [
            CanonicalProductSelect::make(labelKey: 'ops.resources.price_list.item_fields.canonical_product_sku')
                ->nullable()
                ->hintIcon('heroicon-m-information-circle', __('ops.resources.price_list.item_fields.canonical_product_sku_helper')),
            Forms\Components\TextInput::make('product_name')
                ->maxLength(512)
                ->label(__('ops.resources.price_list.item_fields.product_name'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('product_name')),
            Forms\Components\TextInput::make('uom')
                ->maxLength(32)
                ->label(__('ops.resources.price_list.item_fields.uom'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('uom')),
            Forms\Components\TextInput::make('supplier_sku')
                ->maxLength(128)
                ->label(__('ops.resources.price_list.item_fields.supplier_sku'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('supplier_sku')),
            Forms\Components\TextInput::make('unit_price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.0001)
                ->label(__('ops.resources.price_list.item_fields.unit_price'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('unit_price')),
            Forms\Components\TextInput::make('min_qty')
                ->required()
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->label(__('ops.resources.price_list.item_fields.min_qty'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('min_qty')),
            Forms\Components\Select::make('currency')
                ->label(__('ops.resources.price_list.item_fields.currency'))
                ->options(PriceListResource::currencyIsoOptions())
                ->default(fn (): string => $owner->default_currency ?? CurrencyConverter::legacyDefault())
                ->required()
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('currency')),
            Forms\Components\TextInput::make('lead_time_days')
                ->numeric()
                ->minValue(0)
                ->maxValue(3650)
                ->label(__('ops.resources.price_list.item_fields.lead_time_days'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('lead_time_days')),
            Forms\Components\Select::make('inco_term')
                ->label(__('ops.resources.price_list.item_fields.inco_term'))
                ->options(PriceListResource::incoTermOptions())
                ->nullable()
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('inco_term')),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull()
                ->label(__('ops.resources.price_list.item_fields.notes'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('notes')),
        ];
    }

    public static function itemForm(Form $form, PriceList $owner): Form
    {
        return $form->schema(self::itemFormComponents($owner))->columns(2);
    }

    /**
     * Relation manager: query is provided by Filament from the relationship.
     */
    public static function configureRelationTable(Table $table, PriceList $owner): Table
    {
        return self::configureTableCore($table, $owner, false);
    }

    /**
     * Standalone page: explicit query on {@see PriceListItem}.
     */
    public static function configureStandaloneTable(Table $table, PriceList $owner): Table
    {
        return self::configureTableCore($table, $owner, true);
    }

    private static function configureTableCore(Table $table, PriceList $owner, bool $standalone): Table
    {
        $create = Tables\Actions\CreateAction::make()
            ->form(fn (Form $form) => self::itemForm($form, $owner));

        if ($standalone) {
            $create
                ->model(PriceListItem::class)
                ->mutateFormDataUsing(fn (array $data): array => array_merge($data, [
                    'price_list_id' => $owner->id,
                ]));
        }

        $edit = Tables\Actions\EditAction::make()
            ->form(fn (Form $form) => self::itemForm($form, $owner));

        $built = $table
            ->columns([
                Tables\Columns\TextColumn::make('canonicalProduct.sku')
                    ->label(__('ops.resources.price_list.item_fields.canonical_product_sku'))
                    ->placeholder('—')
                    ->url(fn ($record): ?string => $record->canonical_product_id
                        ? CanonicalProductResource::getUrl('edit', ['record' => $record->canonical_product_id])
                        : null)
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label(__('ops.resources.price_list.item_fields.product_name'))
                    ->searchable()
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('product_name')),
                Tables\Columns\TextColumn::make('uom')
                    ->label(__('ops.resources.price_list.item_fields.uom'))
                    ->placeholder('—')
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('uom')),
                Tables\Columns\TextColumn::make('supplier_sku')
                    ->label(__('ops.resources.price_list.item_fields.supplier_sku'))
                    ->placeholder('—')
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('supplier_sku')),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('ops.resources.price_list.item_fields.unit_price'))
                    ->formatStateUsing(fn (mixed $state, Model $record): string => CurrencyFormatter::formatUnitPriceOrLegacy(
                        is_numeric($state) ? $state : null,
                        is_string($record->currency) ? $record->currency : null
                    ))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('unit_price')),
                Tables\Columns\TextColumn::make('min_qty')
                    ->label(__('ops.resources.price_list.item_fields.min_qty'))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('min_qty')),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('ops.resources.price_list.item_fields.currency'))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('currency')),
                Tables\Columns\TextColumn::make('lead_time_days')
                    ->label(__('ops.resources.price_list.item_fields.lead_time_days'))
                    ->placeholder('—')
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('lead_time_days')),
                Tables\Columns\TextColumn::make('inco_term')
                    ->label(__('ops.resources.price_list.item_fields.inco_term'))
                    ->placeholder('—')
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('inco_term')),
            ])
            ->headerActions([
                $create,
            ])
            ->actions([
                $edit,
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        if ($standalone) {
            $built->query(PriceListItem::query()->where('price_list_id', $owner->id));
        }

        return $built;
    }
}
