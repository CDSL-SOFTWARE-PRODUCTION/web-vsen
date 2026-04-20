<?php

namespace App\Filament\Ops\Resources\MasterData\CanonicalProductResource\RelationManagers;

use App\Support\Knowledge\MedicalDeviceDocumentType;
use App\Support\Knowledge\MedicalDeviceDossierClass;
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
            Forms\Components\Select::make('document_type')
                ->required()
                ->options(MedicalDeviceDocumentType::skuLevelOptions())
                ->native(false)
                ->searchable()
                ->label(__('ops.resources.canonical_product_documents.document_type'))
                ->hintIcon('heroicon-m-information-circle')
                ->hintIconTooltip(__('ops.resources.canonical_product_documents.document_type_helper')),
            Forms\Components\Placeholder::make('device_class_reference')
                ->label(__('ops.resources.canonical_product_documents.device_class_reference'))
                ->content($this->resolveDeviceClassLabel())
                ->hintIcon('heroicon-m-information-circle')
                ->hintIconTooltip(__('ops.resources.canonical_product_documents.device_class_reference_helper')),
            Forms\Components\Select::make('status')
                ->required()
                ->options([
                    'required' => __('ops.resources.canonical_product_documents.status.required'),
                    'optional' => __('ops.resources.canonical_product_documents.status.optional'),
                    'provided' => __('ops.resources.canonical_product_documents.status.provided'),
                ])
                ->label(__('ops.common.status')),
            Forms\Components\DatePicker::make('expiry_date')
                ->label(__('ops.resources.canonical_product_documents.expiry_date'))
                ->hintIcon('heroicon-m-information-circle')
                ->hintIconTooltip(function (): ?string {
                    $class = $this->resolveDeviceClass();
                    if (MedicalDeviceDossierClass::isPermanent($class)) {
                        return __('ops.resources.canonical_product_documents.expiry_helper_ab');
                    }
                    if (MedicalDeviceDossierClass::hasFiveYearCycle($class)) {
                        return __('ops.resources.canonical_product_documents.expiry_helper_cd');
                    }

                    return null;
                }),
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
            ->emptyStateHeading(__('ops.resources.canonical_product_documents.empty_heading'))
            ->emptyStateDescription(__('ops.resources.canonical_product_documents.empty_description'))
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label(__('ops.resources.canonical_product_documents.document_type'))
                    ->formatStateUsing(fn (string $state): string => MedicalDeviceDocumentType::skuLevelOptions()[$state] ?? $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_group')
                    ->label(__('ops.resources.canonical_product_documents.device_class_reference'))
                    ->formatStateUsing(fn (?string $state): string => MedicalDeviceDossierClass::optionsForSelect()[$state] ?? (string) $state)
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('validity_policy')
                    ->label(__('ops.resources.canonical_product_documents.validity_policy'))
                    ->getStateUsing(fn ($record): string => MedicalDeviceDossierClass::validityLabel($record->document_group))
                    ->badge()
                    ->color(fn (string $state): string => $state === __('ops.resources.canonical_product_documents.validity_five_years') ? 'warning' : 'success')
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
                Tables\Actions\CreateAction::make()
                    ->label(__('ops.resources.canonical_product_documents.actions.create'))
                    ->modalHeading(__('ops.resources.canonical_product_documents.actions.create')),
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

    private function resolveDeviceClass(): ?string
    {
        /** @var Model $owner */
        $owner = $this->getOwnerRecord();
        $declaration = $owner->medicalDeviceDeclaration;
        if ($declaration === null) {
            return null;
        }

        return $declaration->device_risk_class;
    }

    private function resolveDeviceClassLabel(): string
    {
        $class = $this->resolveDeviceClass();

        return MedicalDeviceDossierClass::optionsForSelect()[$class] ?? '—';
    }
}
