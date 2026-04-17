<?php

namespace App\Filament\Ops\Resources\InventoryLotResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.inventory_ledger.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('action')->badge(),
                Tables\Columns\TextColumn::make('qty_change')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('balance_after')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('supply_order_id')->label(__('ops.resources.supply_order.navigation'))->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
