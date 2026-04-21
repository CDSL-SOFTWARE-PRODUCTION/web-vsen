<?php

namespace App\Filament\Ops\Resources\MasterData;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\MasterData\PriceListResource\Pages;
use App\Filament\Ops\Resources\MasterData\PriceListResource\RelationManagers\PriceListItemsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Demand\PriceList;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PriceListResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = PriceList::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 13;

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

    /**
     * @return array<string, string>
     */
    public static function listScopeOptions(): array
    {
        return [
            PriceList::LIST_SCOPE_SALES => __('ops.resources.price_list.list_scope_options.sales'),
            PriceList::LIST_SCOPE_PROCUREMENT => __('ops.resources.price_list.list_scope_options.procurement'),
            PriceList::LIST_SCOPE_BOTH => __('ops.resources.price_list.list_scope_options.both'),
        ];
    }

    public static function listScopeLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return __('ops.resources.price_list.list_scope_unspecified');
        }

        return self::listScopeOptions()[$state] ?? $state;
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'draft' => __('ops.resources.price_list.status_options.draft'),
            'active' => __('ops.resources.price_list.status_options.active'),
            'archived' => __('ops.resources.price_list.status_options.archived'),
        ];
    }

    public static function statusLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        return self::statusOptions()[$state] ?? $state;
    }

    /**
     * @return array<string, string>
     */
    public static function incoTermOptions(): array
    {
        return [
            'FOB' => 'FOB',
            'EXW' => 'EXW',
            'CIF' => 'CIF',
            'DDP' => 'DDP',
            'DAP' => 'DAP',
        ];
    }

    /**
     * ISO currency codes from config (rates_to_base).
     *
     * @return array<string, string>
     */
    public static function currencyIsoOptions(): array
    {
        $keys = array_keys(config('currency.rates_to_base', []));
        sort($keys);
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $k;
        }

        return $out;
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
                Forms\Components\TextInput::make('list_code')
                    ->maxLength(64)
                    ->unique(ignoreRecord: true)
                    ->label(__('ops.resources.price_list.fields.list_code'))
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('list_code')),
                Forms\Components\Select::make('channel')
                    ->required()
                    ->label(__('ops.resources.price_list.fields.channel'))
                    ->options(self::channelOptions())
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('channel')),
                Forms\Components\Select::make('list_scope')
                    ->label(__('ops.resources.price_list.fields.list_scope'))
                    ->options(self::listScopeOptions())
                    ->placeholder(__('ops.resources.price_list.list_scope_unspecified'))
                    ->nullable()
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('list_scope')),
                Forms\Components\Select::make('status')
                    ->required()
                    ->default('active')
                    ->label(__('ops.resources.price_list.fields.status'))
                    ->options(self::statusOptions())
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('status')),
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
                Forms\Components\Select::make('default_currency')
                    ->label(__('ops.resources.price_list.fields.default_currency'))
                    ->options(self::currencyIsoOptions())
                    ->nullable()
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('default_currency')),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->label(__('ops.resources.price_list.fields.description'))
                    ->hintIcon('heroicon-m-information-circle', self::fieldTooltip('description')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('list_code')
                    ->label(__('ops.resources.price_list.fields.list_code'))
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable()
                    ->tooltip(fn (): string => self::fieldTooltip('list_code')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.price_list.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (): string => self::fieldTooltip('name')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.resources.price_list.fields.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => self::statusLabel($state))
                    ->tooltip(fn (): string => self::fieldTooltip('status')),
                Tables\Columns\TextColumn::make('channel')
                    ->label(__('ops.resources.price_list.fields.channel'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::channelLabel($state))
                    ->tooltip(fn (): string => self::fieldTooltip('channel')),
                Tables\Columns\TextColumn::make('list_scope')
                    ->label(__('ops.resources.price_list.fields.list_scope'))
                    ->formatStateUsing(fn (?string $state): string => self::listScopeLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        PriceList::LIST_SCOPE_SALES => 'info',
                        PriceList::LIST_SCOPE_PROCUREMENT => 'success',
                        PriceList::LIST_SCOPE_BOTH => 'gray',
                        default => 'warning',
                    })
                    ->tooltip(fn (): string => self::fieldTooltip('list_scope')),
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
                    ->sortable()
                    ->tooltip(fn (): string => self::fieldTooltip('valid_to')),
                Tables\Columns\TextColumn::make('default_currency')
                    ->label(__('ops.resources.price_list.fields.default_currency'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn (): string => self::fieldTooltip('default_currency')),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('ops.resources.price_list.lines'))
                    ->tooltip(fn (): string => self::fieldTooltip('lines_count')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('ops.resources.price_list.fields.status'))
                    ->options(self::statusOptions()),
                SelectFilter::make('channel')
                    ->label(__('ops.resources.price_list.fields.channel'))
                    ->options(self::channelOptions()),
                SelectFilter::make('list_scope')
                    ->label(__('ops.resources.price_list.fields.list_scope'))
                    ->options(self::listScopeOptions()),
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
