<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductAliasesRelationManager extends RelationManager
{
    protected static string $relationship = 'aliases';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.product_alias.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('alias_name')
                ->required()
                ->maxLength(512)
                ->label(__('ops.resources.product_alias.alias_name'))
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alias_name')
                    ->label(__('ops.resources.product_alias.alias_name'))
                    ->searchable()
                    ->wrap(),
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
