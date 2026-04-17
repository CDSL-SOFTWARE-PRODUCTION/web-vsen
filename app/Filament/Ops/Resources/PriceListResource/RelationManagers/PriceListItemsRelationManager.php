<?php

namespace App\Filament\Ops\Resources\PriceListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.price_list.items_title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('product_name')->maxLength(512),
            Forms\Components\TextInput::make('unit_price')->required()->numeric()->minValue(0)->prefix('₫'),
            Forms\Components\TextInput::make('min_qty')->required()->numeric()->minValue(1)->default(1),
            Forms\Components\TextInput::make('currency')->maxLength(10)->default('VND'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_name')->searchable(),
                Tables\Columns\TextColumn::make('unit_price')->money('VND'),
                Tables\Columns\TextColumn::make('min_qty'),
                Tables\Columns\TextColumn::make('currency'),
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
