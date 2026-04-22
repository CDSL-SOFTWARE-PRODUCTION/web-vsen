<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources;

use App\Filament\Ops\Clusters\MasterDataCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\MasterData\Resources\ProductFamilyResource\Pages;
use App\Filament\Ops\Clusters\MasterData\Resources\ProductFamilyResource\RelationManagers\CanonicalProductsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Knowledge\MedicalDeviceDeclaration;
use App\Models\Knowledge\ProductFamily;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class ProductFamilyResource extends OpsResource
{
    protected static ?string $model = ProductFamily::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'name';

    

    protected static function visibleInMasterDataStewardSidebar(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.product_family.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label(__('ops.resources.product_family.fields.name')),
            Forms\Components\Select::make('medical_device_declaration_id')
                ->label(__('ops.resources.product_family.fields.medical_device_declaration'))
                ->relationship(
                    'medicalDeviceDeclaration',
                    'declaration_number',
                    fn ($query) => $query->orderBy('declaration_number')
                )
                ->getOptionLabelFromRecordUsing(fn (MedicalDeviceDeclaration $record): string => $record->declaration_number
                    .($record->device_name_official ? ' — '.$record->device_name_official : ''))
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Textarea::make('description')
                ->label(__('ops.resources.product_family.fields.description'))
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.product_family.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('medicalDeviceDeclaration.declaration_number')
                    ->label(__('ops.resources.product_family.fields.medical_device_declaration'))
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('canonical_products_count')
                    ->counts('canonicalProducts')
                    ->label(__('ops.resources.product_family.fields.sku_count')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CanonicalProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductFamilies::route('/'),
            'create' => Pages\CreateProductFamily::route('/create'),
            'edit' => Pages\EditProductFamily::route('/{record}/edit'),
        ];
    }
}
