<?php

namespace App\Filament\Ops\Resources\ReturnOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReturnLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.return_order.lines_title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('item_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('warehouse_code')->required()->maxLength(50)->default('DC'),
            Forms\Components\TextInput::make('quantity')->required()->numeric()->minValue(0)->step(0.001),
            Forms\Components\Select::make('condition')
                ->required()
                ->options([
                    'Good' => __('ops.resources.return_order.condition_good'),
                    'Defective' => __('ops.resources.return_order.condition_defective'),
                ])
                ->default('Good'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')->searchable(),
                Tables\Columns\TextColumn::make('warehouse_code')->badge(),
                Tables\Columns\TextColumn::make('quantity')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('condition')->badge(),
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
