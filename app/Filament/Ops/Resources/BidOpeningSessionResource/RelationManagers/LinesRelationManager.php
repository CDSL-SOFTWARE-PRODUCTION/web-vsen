<?php

namespace App\Filament\Ops\Resources\BidOpeningSessionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.bid_opening_lines.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('source_row_no')
                ->label(__('ops.bid_opening_lines.fields.source_row_no'))
                ->numeric(),
            Forms\Components\TextInput::make('lot_code')
                ->label(__('ops.bid_opening_lines.fields.lot_code'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('item_name')
                ->label(__('ops.bid_opening_lines.fields.item_name'))
                ->maxLength(255),
            Forms\Components\TextInput::make('bidder_identifier')
                ->label(__('ops.bid_opening_lines.fields.bidder_identifier'))
                ->maxLength(255),
            Forms\Components\TextInput::make('bidder_name')
                ->label(__('ops.bid_opening_lines.fields.bidder_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('bid_price')
                ->label(__('ops.bid_opening_lines.fields.bid_price'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('discount_rate')
                ->label(__('ops.bid_opening_lines.fields.discount_rate'))
                ->numeric(),
            Forms\Components\TextInput::make('bid_price_after_discount')
                ->label(__('ops.bid_opening_lines.fields.bid_price_after_discount'))
                ->numeric(),
            Forms\Components\TextInput::make('currency')
                ->label(__('ops.bid_opening_lines.fields.currency'))
                ->default('VND')
                ->maxLength(3),
            Forms\Components\Textarea::make('delivery_commitment')
                ->label(__('ops.bid_opening_lines.fields.delivery_commitment'))
                ->rows(2),
            Forms\Components\TextInput::make('row_fingerprint')
                ->label(__('ops.bid_opening_lines.fields.row_fingerprint'))
                ->required()
                ->maxLength(64),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('source_row_no')
            ->columns([
                Tables\Columns\TextColumn::make('source_row_no')
                    ->label(__('ops.bid_opening_lines.columns.source_row_no'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('lot_code')
                    ->label(__('ops.bid_opening_lines.columns.lot_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidder_name')
                    ->label(__('ops.bid_opening_lines.columns.bidder_name'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('bid_price')
                    ->label(__('ops.bid_opening_lines.columns.bid_price'))
                    ->money('VND'),
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label(__('ops.bid_opening_lines.columns.discount_rate'))
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('bid_price_after_discount')
                    ->label(__('ops.bid_opening_lines.columns.bid_price_after_discount'))
                    ->money('VND')
                    ->placeholder('-'),
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
