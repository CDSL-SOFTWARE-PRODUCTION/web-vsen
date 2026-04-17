<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\PartnerResource\Pages;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 12;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.partner.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'Customer' => __('ops.resources.partner.type_customer'),
                        'Supplier' => __('ops.resources.partner.type_supplier'),
                    ])
                    ->default('Supplier'),
                Forms\Components\Select::make('segment')
                    ->options([
                        'Hospital' => 'Hospital',
                        'Dealer' => 'Dealer',
                        'Clinic' => 'Clinic',
                        'Other' => 'Other',
                    ])
                    ->nullable(),
                Forms\Components\TextInput::make('lead_time_days')->numeric()->minValue(0)->default(7),
                Forms\Components\Textarea::make('reliability_note')->rows(2)->columnSpanFull(),
                Forms\Components\TextInput::make('credit_limit')->numeric()->prefix('₫'),
                Forms\Components\TextInput::make('reserve_ttl_days')->numeric()->minValue(0)->label(__('ops.resources.partner.reserve_ttl_days')),
                Forms\Components\TextInput::make('outstanding_balance_cached')
                    ->numeric()
                    ->prefix('₫')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('max_overdue_days_cached')->numeric()->disabled()->dehydrated(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('segment')->toggleable(),
                Tables\Columns\TextColumn::make('lead_time_days')->label(__('ops.resources.partner.lead_time'))->sortable(),
                Tables\Columns\TextColumn::make('credit_limit')->money('VND')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'Customer' => __('ops.resources.partner.type_customer'),
                    'Supplier' => __('ops.resources.partner.type_supplier'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
