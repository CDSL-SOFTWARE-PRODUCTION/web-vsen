<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources\ProductFamilyResource\RelationManagers;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\MasterDataCluster;

use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CanonicalProductsRelationManager extends RelationManager
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = MasterDataCluster::class;
    protected static string $relationship = 'canonicalProducts';

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
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('raw_name')->limit(40),
                Tables\Columns\TextColumn::make('abc_class')
                    ->label(__('ops.resources.canonical_product.fields.abc_class'))
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query->whereNull('product_family_id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\Action::make('open_full_edit')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->label(__('ops.resources.medical_device_declaration.relation_open_full_edit'))
                    ->url(fn ($record): string => CanonicalProductResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ]);
    }
}
