<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.canonical_product_documents.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('document_type')
                ->required()
                ->maxLength(100)
                ->label(__('ops.resources.canonical_product_documents.document_type')),
            Forms\Components\TextInput::make('document_group')
                ->maxLength(50)
                ->label(__('ops.resources.canonical_product_documents.document_group')),
            Forms\Components\Select::make('status')
                ->required()
                ->options([
                    'required' => __('ops.resources.canonical_product_documents.status.required'),
                    'optional' => __('ops.resources.canonical_product_documents.status.optional'),
                    'provided' => __('ops.resources.canonical_product_documents.status.provided'),
                ])
                ->label(__('ops.common.status')),
            Forms\Components\DatePicker::make('expiry_date')
                ->label(__('ops.resources.canonical_product_documents.expiry_date')),
            Forms\Components\TextInput::make('file_path')
                ->maxLength(255)
                ->label(__('ops.resources.canonical_product_documents.file_path'))
                ->columnSpanFull(),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->label(__('ops.resources.canonical_product_documents.notes'))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label(__('ops.resources.canonical_product_documents.document_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_group')
                    ->label(__('ops.resources.canonical_product_documents.document_group'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.common.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.resources.canonical_product_documents.status.'.$state)),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label(__('ops.resources.canonical_product_documents.expiry_date'))
                    ->date('d/m/Y'),
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
