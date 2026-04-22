<?php

namespace App\Filament\Ops\Clusters\Supply\Pages;

use App\Filament\Ops\Clusters\SupplyCluster;

use App\Filament\Ops\Clusters\MasterData\Resources\PriceListResource;
use App\Filament\Ops\Forms\PriceListItemFilament;
use App\Models\Demand\PriceList;
use App\Models\Demand\PriceListItem;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ProcurementReferencePriceListFlow extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $routePath = '/procurement-reference-price-list/{record?}';

    protected static string $view = 'filament.ops.pages.procurement-reference-price-list-flow';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public ?PriceList $priceList = null;

    /**
     * @var array<string, mixed>
     */
    public array $headerData = [];

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public function mount(?PriceList $record = null): void
    {
        abort_unless(static::canAccess(), 403);

        $this->priceList = $record;

        if ($this->priceList instanceof PriceList) {
            $this->headerData = $this->priceList->only([
                'name',
                'list_code',
                'channel',
                'list_scope',
                'status',
                'description',
                'default_currency',
                'partner_id',
                'valid_from',
                'valid_to',
            ]);
            $this->form->fill($this->headerData);

            return;
        }

        $this->form->fill([
            'list_scope' => PriceList::LIST_SCOPE_PROCUREMENT,
            'status' => 'active',
            'channel' => 'Hospital',
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (FilamentAccess::isMasterDataSteward()) {
            return false;
        }

        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    

    public static function getNavigationLabel(): string
    {
        return __('ops.procurement_reference_price_list.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.procurement_reference_price_list.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.procurement_reference_price_list.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.procurement_reference_price_list.subheading');
    }

    public static function canAccess(): bool
    {
        if (FilamentAccess::isMasterDataSteward()) {
            return false;
        }

        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('full_catalog')
                ->label(__('ops.procurement_reference_price_list.actions.full_list'))
                ->icon('heroicon-o-queue-list')
                ->url(PriceListResource::getUrl('index'))
                ->tooltip(__('ops.procurement_reference_price_list.actions.full_list_tooltip')),
            Action::make('new_list')
                ->label(__('ops.procurement_reference_price_list.actions.new_list'))
                ->icon('heroicon-o-plus')
                ->url(static::getUrl())
                ->visible(fn (): bool => $this->priceList instanceof PriceList),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->headerFormSchema())
            ->statePath('headerData')
            ->model($this->priceList ?? PriceList::class);
    }

    /**
     * Procurement SSOT: inbound reference only (scope limited; partner required).
     *
     * @return array<int, Forms\Components\Component>
     */
    private function headerFormSchema(): array
    {
        $scopeOptions = [
            PriceList::LIST_SCOPE_PROCUREMENT => PriceListResource::listScopeOptions()[PriceList::LIST_SCOPE_PROCUREMENT],
            PriceList::LIST_SCOPE_BOTH => PriceListResource::listScopeOptions()[PriceList::LIST_SCOPE_BOTH],
        ];

        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label(__('ops.resources.price_list.fields.name'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('name')),
            Forms\Components\TextInput::make('list_code')
                ->maxLength(64)
                ->unique(PriceList::class, 'list_code', $this->priceList)
                ->label(__('ops.resources.price_list.fields.list_code'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('list_code')),
            Forms\Components\Select::make('channel')
                ->required()
                ->label(__('ops.resources.price_list.fields.channel'))
                ->options(PriceListResource::channelOptions())
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('channel')),
            Forms\Components\Select::make('list_scope')
                ->required()
                ->label(__('ops.resources.price_list.fields.list_scope'))
                ->options($scopeOptions)
                ->default(PriceList::LIST_SCOPE_PROCUREMENT)
                ->hintIcon('heroicon-m-information-circle', __('ops.procurement_reference_price_list.hints.list_scope_ssot')),
            Forms\Components\Select::make('status')
                ->required()
                ->default('active')
                ->label(__('ops.resources.price_list.fields.status'))
                ->options(PriceListResource::statusOptions())
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('status')),
            Forms\Components\Select::make('partner_id')
                ->label(__('ops.resources.partner.singular'))
                ->options(fn (): array => Partner::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->preload()
                ->required()
                ->hintIcon('heroicon-m-information-circle', __('ops.procurement_reference_price_list.hints.partner_required')),
            Forms\Components\DatePicker::make('valid_from')
                ->label(__('ops.resources.price_list.fields.valid_from'))
                ->displayFormat('d/m/Y')
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('valid_from')),
            Forms\Components\DatePicker::make('valid_to')
                ->label(__('ops.resources.price_list.fields.valid_to'))
                ->displayFormat('d/m/Y')
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('valid_to')),
            Forms\Components\Select::make('default_currency')
                ->label(__('ops.resources.price_list.fields.default_currency'))
                ->options(PriceListResource::currencyIsoOptions())
                ->nullable()
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('default_currency')),
            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->columnSpanFull()
                ->label(__('ops.resources.price_list.fields.description'))
                ->hintIcon('heroicon-m-information-circle', PriceListResource::fieldTooltip('description')),
        ];
    }

    public function saveHeader(): void
    {
        $data = $this->form->getState();
        if (isset($data['list_code']) && $data['list_code'] === '') {
            $data['list_code'] = null;
        }

        if ($this->priceList instanceof PriceList) {
            $this->priceList->update($data);
            $this->priceList->refresh();
            Notification::make()
                ->title(__('ops.procurement_reference_price_list.notifications.header_saved'))
                ->success()
                ->send();

            return;
        }

        $this->priceList = PriceList::create($data);

        Notification::make()
            ->title(__('ops.procurement_reference_price_list.notifications.created'))
            ->success()
            ->send();

        $this->redirect(static::getUrl(['record' => $this->priceList]));
    }

    public function table(Table $table): Table
    {
        if (! $this->priceList instanceof PriceList) {
            return $table
                ->query(PriceListItem::query()->whereRaw('1 = 0'))
                ->columns([
                    Tables\Columns\TextColumn::make('product_name')->label('—'),
                ]);
        }

        return PriceListItemFilament::configureStandaloneTable($table, $this->priceList);
    }
}
