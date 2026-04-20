<?php

namespace App\Filament\Ops\Resources\SupplyOrderResource\RelationManagers;

use App\Models\Knowledge\CanonicalProduct;
use App\Models\Ops\Partner;
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
            Forms\Components\Select::make('canonical_product_id')
                ->label(__('ops.supply_order.lines.fields.canonical_product'))
                ->options(fn (): array => CanonicalProduct::query()->orderBy('sku')->pluck('sku', 'id')->all())
                ->searchable()
                ->preload()
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
            Forms\Components\TextInput::make('supplier_suggestion_source')
                ->label(__('ops.supply_order.lines.fields.supplier_suggestion_source'))
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('item_name')
                ->label(__('ops.supply_order.lines.fields.item_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('required_qty')
                ->label(__('ops.supply_order.lines.fields.required_qty'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('available_qty')
                ->label(__('ops.supply_order.lines.fields.available_qty'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('shortage_qty')
                ->label(__('ops.supply_order.lines.fields.shortage_qty'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('planned_unit_price')
                ->label(__('ops.supply_order.lines.fields.planned_unit_price'))
                ->numeric(),
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
                Tables\Columns\TextColumn::make('supplier_suggestion_source')
                    ->label(__('ops.supply_order.lines.columns.supplier_suggestion_source'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'bidder_identifier' => __('ops.supply_order.lines.suggestion_sources.bidder_identifier'),
                        'bidder_name' => __('ops.supply_order.lines.suggestion_sources.bidder_name'),
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('shortage_qty')
                    ->label(__('ops.supply_order.lines.columns.shortage_qty'))
                    ->formatStateUsing(fn ($state): string => self::formatQuantity($state)),
                Tables\Columns\TextColumn::make('planned_unit_price')
                    ->label(__('ops.supply_order.lines.columns.planned_unit_price'))
                    ->money('VND'),
                Tables\Columns\TextColumn::make('reference_unit_price')
                    ->label(__('ops.supply_order.lines.columns.reference_unit_price'))
                    ->money('VND')
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

    private static function formatQuantity($state): string
    {
        if (! is_numeric($state)) {
            return (string) $state;
        }

        $number = (float) $state;
        if (fmod($number, 1.0) === 0.0) {
            return number_format($number, 0, ',', '.');
        }

        $formatted = number_format($number, 3, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
