<?php

namespace App\Filament\Ops\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('line_no')
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('uom')
                ->maxLength(50),
            Forms\Components\TextInput::make('quantity')
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('status')
                ->required()
                ->maxLength(40),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('line_no')
            ->columns([
                Tables\Columns\TextColumn::make('line_no')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->limit(45),
                Tables\Columns\TextColumn::make('uom')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}

