<?php

namespace App\Filament\Ops\Resources\MasterData;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\MasterData\RequirementResource\Pages;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Knowledge\Requirement;
use App\Support\Ops\FilamentAccess;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class RequirementResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = Requirement::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 14;

    protected static function opsNavigationClusterKey(): string
    {
        return 'master_data';
    }

    protected static function visibleInMasterDataStewardSidebar(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.requirement.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (Filament::getCurrentPanel()?->getId() === 'data-steward') {
            return false;
        }

        return parent::shouldRegisterNavigation();
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
                Forms\Components\Section::make(__('ops.resources.requirement.navigation'))
                    ->description(__('ops.resources.requirement.master_rule_helper'))
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->label(__('ops.resources.requirement.code')),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'ISO_13485' => 'ISO 13485',
                                'CE' => 'CE',
                                'FSC' => 'FSC',
                                'Catalog' => 'Catalog',
                            ]),
                        Forms\Components\TextInput::make('name')->maxLength(255),
                        Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('name')->limit(40),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'ISO_13485' => 'ISO 13485',
                    'CE' => 'CE',
                    'FSC' => 'FSC',
                    'Catalog' => 'Catalog',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequirements::route('/'),
            'create' => Pages\CreateRequirement::route('/create'),
            'edit' => Pages\EditRequirement::route('/{record}/edit'),
        ];
    }
}
