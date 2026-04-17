<?php

namespace App\Filament\Ops\Resources\PriceListResource\RelationManagers;

use App\Filament\Ops\Resources\CanonicalProductResource;
use App\Filament\Ops\Resources\PriceListResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static function formatMoneyByCurrency(mixed $unitPrice, mixed $currency): string
    {
        if (! is_numeric($unitPrice)) {
            return '';
        }

        $normalizedPrice = (float) $unitPrice;
        $formattedPrice = number_format($normalizedPrice, 2, '.', ',');
        $formattedPrice = rtrim(rtrim($formattedPrice, '0'), '.');
        $currencyCode = is_string($currency) && $currency !== '' ? strtoupper($currency) : 'N/A';

        return $formattedPrice.' '.$currencyCode;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.price_list.items_title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('canonical_product_id')
                ->label(__('ops.resources.price_list.item_fields.canonical_product_sku'))
                ->relationship('canonicalProduct', 'sku')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText(__('ops.resources.price_list.item_fields.canonical_product_sku_helper')),
            Forms\Components\TextInput::make('product_name')
                ->maxLength(512)
                ->label(__('ops.resources.price_list.item_fields.product_name'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('product_name')),
            Forms\Components\TextInput::make('unit_price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->label(__('ops.resources.price_list.item_fields.unit_price'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('unit_price')),
            Forms\Components\TextInput::make('min_qty')
                ->required()
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->label(__('ops.resources.price_list.item_fields.min_qty'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('min_qty')),
            Forms\Components\TextInput::make('currency')
                ->maxLength(10)
                ->default('USD')
                ->label(__('ops.resources.price_list.item_fields.currency'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('currency')),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('ops.resources.price_list.item_fields.unit_price'))
                    ->formatStateUsing(fn (mixed $state, Model $record): string => self::formatMoneyByCurrency($state, $record->currency))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('unit_price')),
                Tables\Columns\TextColumn::make('min_qty')
                    ->label(__('ops.resources.price_list.item_fields.min_qty'))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('min_qty')),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('ops.resources.price_list.item_fields.currency'))
                    ->tooltip(fn (): string => PriceListResource::fieldTooltip('currency')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
