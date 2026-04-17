<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\PriceListResource\Pages;
use App\Filament\Ops\Resources\PriceListResource\RelationManagers\PriceListItemsRelationManager;
use App\Models\Demand\PriceList;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 13;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.price_list.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.price_list.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.price_list.plural_model_label');
    }

    /**
     * Long help for price list fields (table column hover + form hint icon).
     * Intentionally does not claim browser/Filament shortcuts: Ctrl+S is not bound here and may trigger the browser “Save page”.
     */
    public static function fieldTooltip(string $tooltipsKey): string
    {
        return __('ops.resources.price_list.tooltips.'.$tooltipsKey);
    }

    public static function channelOptions(): array
    {
        return [
            'Hospital' => __('ops.resources.price_list.channel_options.Hospital'),
            'Dealer' => __('ops.resources.price_list.channel_options.Dealer'),
            'Tender' => __('ops.resources.price_list.channel_options.Tender'),
            'Retail' => __('ops.resources.price_list.channel_options.Retail'),
        ];
    }

    public static function channelLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        $key = 'ops.resources.price_list.channel_options.'.$state;

        return __($key);
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
                    ->required()
                    ->maxLength(255)
                    ->label(__('ops.resources.price_list.fields.name'))
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('name')),
                Forms\Components\Select::make('channel')
                    ->required()
                    ->label(__('ops.resources.price_list.fields.channel'))
                    ->options(self::channelOptions())
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('channel')),
                Forms\Components\Select::make('partner_id')
                    ->label(__('ops.resources.partner.singular'))
                    ->options(fn (): array => Partner::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('partner')),
                Forms\Components\DatePicker::make('valid_from')
                    ->label(__('ops.resources.price_list.fields.valid_from'))
                    ->displayFormat('d/m/Y')
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('valid_from')),
                Forms\Components\DatePicker::make('valid_to')
                    ->label(__('ops.resources.price_list.fields.valid_to'))
                    ->displayFormat('d/m/Y')
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('valid_to')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.price_list.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (): string => self::fieldTooltip('name')),
                Tables\Columns\TextColumn::make('channel')
                    ->label(__('ops.resources.price_list.fields.channel'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::channelLabel($state))
                    ->tooltip(fn (): string => self::fieldTooltip('channel')),
                Tables\Columns\TextColumn::make('partner.name')
                    ->label(__('ops.resources.partner.singular'))
                    ->toggleable()
                    ->tooltip(fn (): string => self::fieldTooltip('partner')),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('ops.resources.price_list.fields.valid_from'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->tooltip(fn (): string => self::fieldTooltip('valid_from')),
                Tables\Columns\TextColumn::make('valid_to')
                    ->label(__('ops.resources.price_list.fields.valid_to'))
                    ->date('d/m/Y')
                    ->tooltip(fn (): string => self::fieldTooltip('valid_to')),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('ops.resources.price_list.lines'))
                    ->tooltip(fn (): string => self::fieldTooltip('lines_count')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PriceListItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
