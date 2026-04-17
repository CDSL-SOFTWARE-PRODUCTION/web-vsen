<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers;

use App\Filament\Ops\Resources\PriceListResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LinkedPriceListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceListItems';

    public function isReadOnly(): bool
    {
        return true;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.canonical_product.ref_price_lists_title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('priceList'))
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('priceList.name')
                    ->label(__('ops.resources.canonical_product.ref_price_list_name'))
                    ->url(fn (Model $record): string => PriceListResource::getUrl('edit', ['record' => $record->price_list_id]))
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label(__('ops.resources.price_list.item_fields.product_name'))
                    ->limit(48),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('ops.resources.price_list.item_fields.unit_price'))
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('ops.resources.price_list.item_fields.currency')),
            ])
            ->defaultSort('price_list_id')
            ->emptyStateHeading(__('ops.resources.canonical_product.ref_price_lists_empty_heading'))
            ->emptyStateDescription(__('ops.resources.canonical_product.ref_price_lists_empty_desc'));
    }
}
