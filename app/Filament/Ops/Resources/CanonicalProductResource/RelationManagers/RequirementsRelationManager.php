<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'requirements';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.requirement.plural_short');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('ops.resources.requirement.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('ops.resources.requirement.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.requirement.name'))
                    ->limit(40),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
