<?php

namespace App\Filament\Ops\Clusters\Supply\Resources\SupplyOrderResource\RelationManagers;

use App\Filament\Ops\Forms\CanonicalProductSelect;
use App\Models\Ops\Partner;
use App\Support\Currency\CurrencyConverter;
use App\Support\Currency\CurrencyFormatter;
use App\Support\Supply\ProcurementQuantityFormatter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public function form(Form $form): Form
    {
        return $form->schema([
            CanonicalProductSelect::make(labelKey: 'ops.supply_order.lines.fields.canonical_product')
                ->required(),
            Forms\Components\Select::make('supplier_partner_id')
                ->label(__('ops.supply_order.lines.fields.supplier_partner'))
                ->options(fn (): array => Partner::query()
                    ->where('type', 'Supplier')
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('item_name')
                ->label(__('ops.supply_order.lines.fields.item_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('required_qty')
                ->label(__('ops.supply_order.lines.fields.required_qty'))
                ->required()
                ->numeric()
                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state): void {
                    $component->state(self::normalizeQuantityInputState($state));
                })
                ->dehydrateStateUsing(fn ($state) => self::normalizeQuantityInputState($state)),
            Forms\Components\TextInput::make('available_qty')
                ->label(__('ops.supply_order.lines.fields.available_qty'))
                ->required()
                ->numeric()
                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state): void {
                    $component->state(self::normalizeQuantityInputState($state));
                })
                ->dehydrateStateUsing(fn ($state) => self::normalizeQuantityInputState($state)),
            Forms\Components\TextInput::make('shortage_qty')
                ->label(__('ops.supply_order.lines.fields.shortage_qty'))
                ->required()
                ->numeric()
                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state): void {
                    $component->state(self::normalizeQuantityInputState($state));
                })
                ->dehydrateStateUsing(fn ($state) => self::normalizeQuantityInputState($state)),
            Forms\Components\TextInput::make('planned_unit_price')
                ->label(__('ops.supply_order.lines.fields.planned_unit_price'))
                ->numeric()
                ->step(0.0001)
                ->minValue(0),
            Forms\Components\TextInput::make('reference_unit_price')
                ->label(__('ops.supply_order.lines.fields.reference_unit_price'))
                ->numeric()
                ->disabled()
                ->dehydrated(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('canonicalProduct.sku')
                    ->label(__('ops.supply_order.lines.columns.canonical_product'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('item_name')
                    ->label(__('ops.supply_order.lines.columns.item_name'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('supplierPartner.name')
                    ->label(__('ops.supply_order.lines.columns.supplier_partner'))
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('required_qty')
                    ->label(__('ops.supply_order.lines.columns.required_qty'))
                    ->formatStateUsing(fn ($state): string => ProcurementQuantityFormatter::formatDisplay($state)),
                Tables\Columns\TextColumn::make('available_qty')
                    ->label(__('ops.supply_order.lines.columns.available_qty'))
                    ->formatStateUsing(fn ($state): string => ProcurementQuantityFormatter::formatDisplay($state)),
                Tables\Columns\TextColumn::make('shortage_qty')
                    ->label(__('ops.supply_order.lines.columns.shortage_qty'))
                    ->formatStateUsing(fn ($state): string => ProcurementQuantityFormatter::formatDisplay($state)),
                Tables\Columns\TextColumn::make('planned_unit_price')
                    ->label(__('ops.supply_order.lines.columns.planned_unit_price'))
                    ->formatStateUsing(fn ($state): string => is_numeric($state)
                        ? CurrencyFormatter::formatUnitPrice($state, CurrencyConverter::legacyDefault())
                        : '-'),
                Tables\Columns\TextColumn::make('reference_unit_price')
                    ->label(__('ops.supply_order.lines.columns.reference_unit_price'))
                    ->formatStateUsing(fn ($state): string => is_numeric($state)
                        ? CurrencyFormatter::formatUnitPrice($state, CurrencyConverter::legacyDefault())
                        : '-')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('price_deviation_flag')
                    ->label(__('ops.supply_order.lines.columns.price_deviation_flag'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.supply_order.lines.columns.status'))
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    private static function normalizeQuantityInputState($state): string
    {
        if (! is_numeric($state)) {
            return (string) $state;
        }

        $number = (float) $state;
        if (fmod($number, 1.0) === 0.0) {
            return (string) (int) $number;
        }

        $normalized = number_format($number, 3, '.', '');

        return rtrim(rtrim($normalized, '0'), '.');
    }
}
