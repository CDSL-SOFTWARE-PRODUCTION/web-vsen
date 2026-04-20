<?php

namespace App\Filament\Ops\Resources\MasterData\MedicalDeviceDeclarationResource\RelationManagers;

use App\Support\Knowledge\MedicalDeviceDocumentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.medical_device_declaration_documents.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('document_type')
                ->required()
                ->options(MedicalDeviceDocumentType::declarationLevelOptions())
                ->native(false)
                ->searchable()
                ->label(__('ops.resources.medical_device_declaration_documents.document_type'))
                ->hintIcon('heroicon-m-information-circle')
                ->hintIconTooltip(__('ops.resources.medical_device_declaration_documents.document_type_helper')),
            Forms\Components\Select::make('status')
                ->required()
                ->options([
                    'required' => __('ops.resources.medical_device_declaration_documents.status.required'),
                    'optional' => __('ops.resources.medical_device_declaration_documents.status.optional'),
                    'provided' => __('ops.resources.medical_device_declaration_documents.status.provided'),
                ])
                ->label(__('ops.common.status')),
            Forms\Components\DatePicker::make('expiry_date')
                ->label(__('ops.resources.medical_device_declaration_documents.expiry_date')),
            Forms\Components\TextInput::make('file_path')
                ->maxLength(255)
                ->label(__('ops.resources.medical_device_declaration_documents.file_path'))
                ->columnSpanFull(),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->label(__('ops.resources.medical_device_declaration_documents.notes'))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label(__('ops.resources.medical_device_declaration_documents.document_type'))
                    ->formatStateUsing(fn (string $state): string => MedicalDeviceDocumentType::declarationLevelOptions()[$state] ?? $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.common.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.resources.medical_device_declaration_documents.status.'.$state)),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label(__('ops.resources.medical_device_declaration_documents.expiry_date'))
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
