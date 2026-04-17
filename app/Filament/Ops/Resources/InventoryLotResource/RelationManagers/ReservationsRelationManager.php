<?php

namespace App\Filament\Ops\Resources\InventoryLotResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.inventory_reservation.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('order_item_id')
                ->label(__('ops.resources.inventory_reservation.order_item'))
                ->relationship('orderItem', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('reserved_qty')->required()->numeric()->minValue(0)->step(0.001),
            Forms\Components\Select::make('status')
                ->options([
                    'Reserved' => 'Reserved',
                    'Released' => 'Released',
                    'Expired' => 'Expired',
                ])
                ->default('Reserved')
                ->required(),
            Forms\Components\DateTimePicker::make('reserved_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
            Forms\Components\DateTimePicker::make('released_at'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.order.order_code')
                    ->label(__('ops.resources.order.navigation'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('orderItem.name')
                    ->label(__('ops.common.item'))
                    ->limit(32),
                Tables\Columns\TextColumn::make('reserved_qty')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('reserved_at')->dateTime(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
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
