<?php

namespace App\Filament\Ops\Resources\MasterData\MedicalDeviceDeclarationResource\RelationManagers;

use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
use App\Models\Knowledge\ProductFamily;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CanonicalProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'canonicalProducts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.medical_device_declaration.relation_canonical_products');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(64)
                    ->label(__('ops.resources.canonical_product.fields.sku')),
                Forms\Components\TextInput::make('raw_name')
                    ->required()
                    ->maxLength(512)
                    ->label(__('ops.resources.canonical_product.fields.raw_name')),
                Forms\Components\Select::make('abc_class')
                    ->label(__('ops.resources.canonical_product.fields.abc_class'))
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                    ])
                    ->nullable(),
                Forms\Components\Select::make('product_family_id')
                    ->label(__('ops.resources.canonical_product.fields.product_family'))
                    ->options(function (): array {
                        $declarationId = $this->getOwnerRecord()->getKey();

                        return ProductFamily::query()
                            ->where('medical_device_declaration_id', $declarationId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('raw_name')->limit(50),
                Tables\Columns\TextColumn::make('abc_class')
                    ->label(__('ops.resources.canonical_product.fields.abc_class'))
                    ->badge(),
                Tables\Columns\TextColumn::make('productFamily.name')
                    ->label(__('ops.resources.canonical_product.fields.product_family'))
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('ops.resources.medical_device_declaration.relation_create_sku')),
                Tables\Actions\AssociateAction::make()
                    ->label(__('ops.resources.medical_device_declaration.relation_associate_sku'))
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->whereNull('medical_device_declaration_id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make()
                    ->label(__('ops.resources.medical_device_declaration.relation_dissociate_sku')),
                Tables\Actions\Action::make('open_full_edit')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->label(__('ops.resources.medical_device_declaration.relation_open_full_edit'))
                    ->url(fn ($record): string => CanonicalProductResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make()
                        ->label(__('ops.resources.medical_device_declaration.relation_dissociate_bulk')),
                ]),
            ]);
    }
}
