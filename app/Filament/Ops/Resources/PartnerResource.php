<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\PartnerResource\Pages;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = Partner::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 12;

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
        return __('ops.resources.partner.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.partner.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.partner.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('ops.resources.partner.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label(__('ops.resources.partner.fields.type'))
                    ->required()
                    ->options([
                        'Customer' => __('ops.resources.partner.type_customer'),
                        'Supplier' => __('ops.resources.partner.type_supplier'),
                    ])
                    ->default('Supplier'),
                Forms\Components\Select::make('segment')
                    ->label(__('ops.resources.partner.fields.segment'))
                    ->options([
                        'Hospital' => __('ops.resources.partner.segment_options.Hospital'),
                        'Dealer' => __('ops.resources.partner.segment_options.Dealer'),
                        'Clinic' => __('ops.resources.partner.segment_options.Clinic'),
                        'Other' => __('ops.resources.partner.segment_options.Other'),
                    ])
                    ->nullable(),
                Forms\Components\TextInput::make('lead_time_days')
                    ->label(__('ops.resources.partner.fields.lead_time_days'))
                    ->numeric()
                    ->minValue(0)
                    ->default(7),
                Forms\Components\Textarea::make('reliability_note')
                    ->label(__('ops.resources.partner.fields.reliability_note'))
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('credit_limit')
                    ->label(__('ops.resources.partner.fields.credit_limit'))
                    ->numeric()
                    ->prefix('₫'),
                Forms\Components\TextInput::make('reserve_ttl_days')
                    ->numeric()
                    ->minValue(0)
                    ->label(__('ops.resources.partner.reserve_ttl_days')),
                Forms\Components\TextInput::make('outstanding_balance_cached')
                    ->label(__('ops.resources.partner.fields.outstanding_balance_cached'))
                    ->numeric()
                    ->prefix('₫')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('max_overdue_days_cached')
                    ->label(__('ops.resources.partner.fields.max_overdue_days_cached'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.partner.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('ops.resources.partner.fields.type'))
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            'Customer' => __('ops.resources.partner.type_customer'),
                            'Supplier' => __('ops.resources.partner.type_supplier'),
                            default => (string) $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('segment')
                    ->label(__('ops.resources.partner.fields.segment'))
                    ->formatStateUsing(function (?string $state): ?string {
                        if ($state === null || $state === '') {
                            return null;
                        }

                        return match ($state) {
                            'Hospital' => __('ops.resources.partner.segment_options.Hospital'),
                            'Dealer' => __('ops.resources.partner.segment_options.Dealer'),
                            'Clinic' => __('ops.resources.partner.segment_options.Clinic'),
                            'Other' => __('ops.resources.partner.segment_options.Other'),
                            default => $state,
                        };
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lead_time_days')
                    ->label(__('ops.resources.partner.lead_time'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->label(__('ops.resources.partner.fields.credit_limit'))
                    ->money('VND')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('ops.resources.partner.table.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('ops.resources.partner.filters.type'))
                    ->options([
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
