<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources;

use App\Filament\Ops\Clusters\MasterDataCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\MasterData\Resources\MedicalDeviceDeclarationResource\Pages;
use App\Filament\Ops\Clusters\MasterData\Resources\MedicalDeviceDeclarationResource\RelationManagers\CanonicalProductsRelationManager;
use App\Filament\Ops\Clusters\MasterData\Resources\MedicalDeviceDeclarationResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Knowledge\MedicalDeviceDeclaration;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class MedicalDeviceDeclarationResource extends OpsResource
{
    protected static ?string $model = MedicalDeviceDeclaration::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'declaration_number';

    

    protected static function visibleInMasterDataStewardSidebar(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        $key = 'ops.resources.medical_device_declaration.navigation';
        $label = __($key);

        if ($label !== $key) {
            return $label;
        }

        return app()->getLocale() === 'vi'
            ? 'Hồ sơ công bố TBYT'
            : 'Medical device declarations';
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.medical_device_declaration.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.medical_device_declaration.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL)
            || FilamentAccess::canAccessDataStewardPanel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.resources.medical_device_declaration.section_core'))
                    ->schema([
                        Forms\Components\TextInput::make('declaration_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(128)
                            ->label(__('ops.resources.medical_device_declaration.declaration_number')),
                        Forms\Components\DatePicker::make('declared_on')
                            ->label(__('ops.resources.medical_device_declaration.declared_on')),
                        Forms\Components\TextInput::make('issuer')
                            ->maxLength(255)
                            ->label(__('ops.resources.medical_device_declaration.issuer')),
                        Forms\Components\Select::make('device_risk_class')
                            ->label(__('ops.resources.medical_device_declaration.device_risk_class'))
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'C' => 'C',
                                'D' => 'D',
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('device_name_official')
                            ->maxLength(512)
                            ->label(__('ops.resources.medical_device_declaration.device_name_official'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('ops.resources.medical_device_declaration.section_declaring'))
                    ->schema([
                        Forms\Components\Textarea::make('declaring_organization')
                            ->rows(2)
                            ->label(__('ops.resources.medical_device_declaration.declaring_organization')),
                        Forms\Components\Textarea::make('declaring_address')
                            ->rows(2)
                            ->label(__('ops.resources.medical_device_declaration.declaring_address')),
                        Forms\Components\TextInput::make('internal_reference_code')
                            ->maxLength(128)
                            ->label(__('ops.resources.medical_device_declaration.internal_reference_code')),
                        Forms\Components\DatePicker::make('internal_reference_date')
                            ->label(__('ops.resources.medical_device_declaration.internal_reference_date')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('ops.resources.medical_device_declaration.section_owner_quality'))
                    ->schema([
                        Forms\Components\Textarea::make('quality_standard')
                            ->rows(2)
                            ->label(__('ops.resources.medical_device_declaration.quality_standard'))
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('legal_owner_name')
                            ->rows(2)
                            ->label(__('ops.resources.medical_device_declaration.legal_owner_name')),
                        Forms\Components\Textarea::make('legal_owner_address')
                            ->rows(3)
                            ->label(__('ops.resources.medical_device_declaration.legal_owner_address'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('ops.resources.medical_device_declaration.section_notes'))
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->label(__('ops.resources.medical_device_declaration.notes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('declared_on', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('declaration_number')
                    ->label(__('ops.resources.medical_device_declaration.declaration_number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('declared_on')
                    ->label(__('ops.resources.medical_device_declaration.declared_on'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_risk_class')
                    ->label(__('ops.resources.medical_device_declaration.device_risk_class'))
                    ->badge(),
                Tables\Columns\TextColumn::make('device_name_official')
                    ->label(__('ops.resources.medical_device_declaration.device_name_official'))
                    ->limit(40),
                Tables\Columns\TextColumn::make('canonical_products_count')
                    ->counts('canonicalProducts')
                    ->label(__('ops.resources.medical_device_declaration.sku_count')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device_risk_class')
                    ->label(__('ops.resources.medical_device_declaration.device_risk_class'))
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CanonicalProductsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalDeviceDeclarations::route('/'),
            'create' => Pages\CreateMedicalDeviceDeclaration::route('/create'),
            'edit' => Pages\EditMedicalDeviceDeclaration::route('/{record}/edit'),
        ];
    }
}
