<?php

namespace App\Filament\Ops\Pages;

use App\Filament\Ops\Resources\MasterData\PriceListResource;
use App\Filament\Ops\Widgets\SupplySelectionOverviewWidget;
use App\Models\Demand\PriceListItem;
use App\Models\Ops\Partner;
use App\Models\Supply\SupplyOrder;
use App\Models\Supply\SupplyOrderLine;
use App\Models\Supply\SupplyOrderLineSupplierQuote;
use App\Support\Ops\FilamentAccess;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SupplySelectionAnalysis extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.ops.pages.supply-selection-analysis';

    protected static ?int $navigationSort = 5;

    public ?array $comparisonFormData = [];

    private ?string $comparisonRowsByLineIdCacheKey = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $comparisonRowsByLineIdMemo = null;

    private ?string $priceListByNormCacheKey = null;

    /**
     * Latest unit price per normalized product label and supplier, from price list items (newest row wins).
     *
     * @var array<string, array<int, float>>|null
     */
    private ?array $priceListByNormMemo = null;

    public function mount(): void
    {
        $orderId = request()->query('order_id');
        $this->form->fill([
            'order_id' => is_numeric($orderId) ? (int) $orderId : null,
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (FilamentAccess::isMasterDataSteward()) {
            return false;
        }

        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.supply');
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.supply_selection_analysis.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.supply_selection_analysis.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.supply_selection_analysis.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.supply_selection_analysis.subheading');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SupplySelectionOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_quotes')
                ->label(__('ops.supply_selection_analysis.actions.manage_quotes'))
                ->icon('heroicon-o-currency-dollar')
                ->url(PriceListResource::getUrl('index')),
            Action::make('refresh')
                ->label(__('ops.supply_selection_analysis.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn (): null => null),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label(__('ops.supply_selection_analysis.compare.supply_order'))
                            ->options(fn (): array => SupplyOrder::query()
                                ->orderByDesc('updated_at')
                                ->limit(300)
                                ->get(['id', 'supply_order_code'])
                                ->mapWithKeys(
                                    fn (SupplyOrder $supplyOrder): array => [
                                        $supplyOrder->id => (string) $supplyOrder->supply_order_code,
                                    ]
                                )
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                    ]),
            ])
            ->statePath('comparisonFormData');
    }

    /**
     * @return list<array{
     *   order_line:string,
     *   canonical_sku:string,
     *   qty:float,
     *   prices:array<int,array{unit_price:float|null,total:float|null}>,
     *   best_supplier_id:int|null
     * }>
     */
    public function getComparisonRows(): array
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return [];
        }

        $suppliers = $this->comparisonSuppliers();
        if ($suppliers->isEmpty()) {
            return [];
        }

        $supplyOrder->loadMissing('lines.canonicalProduct');
        $latestQuotes = $this->latestQuotesByProductAndSupplier($supplyOrder);
        $supplierIds = $suppliers->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $rows = [];
        foreach ($supplyOrder->lines as $line) {
            $qty = is_numeric($line->shortage_qty) ? (float) $line->shortage_qty : 0.0;
            $priceBySupplier = [];
            $bestSupplierId = null;
            $bestTotal = null;

            foreach ($supplierIds as $supplierId) {
                $quoteKey = $this->quoteKeyForLineLookup($line, $supplierId);
                $unitPrice = $latestQuotes[$quoteKey] ?? null;

                $lineTotal = $unitPrice === null ? null : ($unitPrice * $qty);
                $priceBySupplier[$supplierId] = [
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];

                if ($lineTotal !== null && ($bestTotal === null || $lineTotal < $bestTotal)) {
                    $bestTotal = $lineTotal;
                    $bestSupplierId = $supplierId;
                }
            }

            $rows[] = [
                'line_id' => (int) $line->id,
                'order_line' => trim((string) $line->item_name),
                'canonical_sku' => (string) ($line->canonicalProduct?->sku ?? ''),
                'image_url' => $line->canonicalProduct?->resolvedImageUrls()[0] ?? null,
                'qty' => $qty,
                'prices' => $priceBySupplier,
                'best_supplier_id' => $bestSupplierId,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int,float>
     */
    public function getSupplierGrandTotals(): array
    {
        $rows = $this->getComparisonRows();
        $grandTotals = [];
        foreach ($this->comparisonSuppliers() as $supplier) {
            $grandTotals[(int) $supplier->id] = 0.0;
        }

        foreach ($rows as $row) {
            foreach ($grandTotals as $supplierId => $total) {
                $lineTotal = $row['prices'][$supplierId]['total'] ?? null;
                if ($lineTotal === null) {
                    continue;
                }

                $grandTotals[$supplierId] += $lineTotal;
            }
        }

        return $grandTotals;
    }

    /**
     * Suppliers to show as matrix columns: lines on this order, any historical
     * {@see SupplyOrderLine} quotes for the same canonical products, and master
     * {@see PriceListItem} rows (partner comes from the parent price list).
     *
     * @return Collection<int, Partner>
     */
    public function comparisonSuppliers(): Collection
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return collect();
        }

        $supplyOrder->loadMissing('lines');

        $canonicalProductIds = $supplyOrder->lines
            ->pluck('canonical_product_id')
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $supplierIds = collect();

        foreach ($supplyOrder->lines as $line) {
            if (is_numeric($line->supplier_partner_id)) {
                $supplierIds->push((int) $line->supplier_partner_id);
            }
        }

        if ($canonicalProductIds !== []) {
            $fromOtherSupplyLines = SupplyOrderLine::query()
                ->whereIn('canonical_product_id', $canonicalProductIds)
                ->whereNotNull('supplier_partner_id')
                ->distinct()
                ->pluck('supplier_partner_id')
                ->map(fn ($id): int => (int) $id);
            $supplierIds = $supplierIds->merge($fromOtherSupplyLines);

            $fromPriceLists = PriceListItem::query()
                ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
                ->whereIn('price_list_items.canonical_product_id', $canonicalProductIds)
                ->whereNotNull('price_lists.partner_id')
                ->distinct()
                ->pluck('price_lists.partner_id')
                ->map(fn ($id): int => (int) $id);
            $supplierIds = $supplierIds->merge($fromPriceLists);
        }

        $lineNameNorms = $supplyOrder->lines
            ->map(fn (SupplyOrderLine $line): string => $this->normalizeProductLabel($line->item_name))
            ->filter(fn (string $norm): bool => $norm !== '')
            ->unique();
        $byNorm = $this->priceListLatestByNormalizedNameAndSupplierMap();
        foreach ($lineNameNorms as $norm) {
            if (! isset($byNorm[$norm])) {
                continue;
            }
            foreach (array_keys($byNorm[$norm]) as $partnerId) {
                $supplierIds->push((int) $partnerId);
            }
        }

        $lineIds = $supplyOrder->lines->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();
        if ($lineIds !== []) {
            $fromManualQuotes = SupplyOrderLineSupplierQuote::query()
                ->whereIn('supply_order_line_id', $lineIds)
                ->whereNotNull('partner_id')
                ->distinct()
                ->pluck('partner_id')
                ->map(fn ($id): int => (int) $id);
            $supplierIds = $supplierIds->merge($fromManualQuotes);
        }

        $supplierIds = $supplierIds->unique()->values()->all();

        if ($supplierIds === []) {
            return collect();
        }

        return Partner::query()
            ->whereIn('id', $supplierIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function selectedSupplyOrderCode(): ?string
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return null;
        }

        return (string) $supplyOrder->supply_order_code;
    }

    private function supplierGroupLabel(Partner $supplier): HtmlString
    {
        $term = $this->resolvePricingTerm($supplier);

        return new HtmlString(
            '<div class="leading-tight text-center"><div class="font-semibold">'.e((string) $supplier->name).'</div><div class="text-xs text-gray-500 dark:text-gray-400">'.e($term).'</div></div>'
        );
    }

    /**
     * @return array<int, array{
     *   line_id:int,
     *   order_line:string,
     *   canonical_sku:string,
     *   image_url:string|null,
     *   qty:float,
     *   prices:array<int,array{unit_price:float|null,total:float|null}>,
     *   best_supplier_id:int|null
     * }>
     */
    private function comparisonRowsByLineId(): array
    {
        $cacheKey = (string) ($this->comparisonFormData['order_id'] ?? '');
        if ($this->comparisonRowsByLineIdMemo !== null && $this->comparisonRowsByLineIdCacheKey === $cacheKey) {
            return $this->comparisonRowsByLineIdMemo;
        }

        $indexed = [];
        foreach ($this->getComparisonRows() as $row) {
            $lineId = $row['line_id'] ?? null;
            if (! is_numeric($lineId)) {
                continue;
            }

            $indexed[(int) $lineId] = $row;
        }

        $this->comparisonRowsByLineIdCacheKey = $cacheKey;
        $this->comparisonRowsByLineIdMemo = $indexed;

        return $indexed;
    }

    private function selectedSupplyOrder(): ?SupplyOrder
    {
        $supplyOrderId = $this->comparisonFormData['order_id'] ?? null;
        if (! is_numeric($supplyOrderId)) {
            return null;
        }

        return SupplyOrder::query()->find((int) $supplyOrderId);
    }

    /**
     * @return array<string, float>
     */
    private function latestQuotesByProductAndSupplier(SupplyOrder $supplyOrder): array
    {
        $canonicalProductIds = $supplyOrder->lines
            ->pluck('canonical_product_id')
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $latestByKey = [];

        if ($canonicalProductIds !== []) {
            $quotes = SupplyOrderLine::query()
                ->select(['canonical_product_id', 'supplier_partner_id', 'planned_unit_price', 'updated_at', 'id'])
                ->whereIn('canonical_product_id', $canonicalProductIds)
                ->whereNotNull('supplier_partner_id')
                ->whereNotNull('planned_unit_price')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            foreach ($quotes as $quote) {
                $canonicalProductId = $quote->canonical_product_id;
                $supplierId = $quote->supplier_partner_id;
                if (! is_numeric($canonicalProductId) || ! is_numeric($supplierId) || ! is_numeric($quote->planned_unit_price)) {
                    continue;
                }

                $key = $this->quoteKey((int) $canonicalProductId, (int) $supplierId);
                if (isset($latestByKey[$key])) {
                    continue;
                }

                $latestByKey[$key] = (float) $quote->planned_unit_price;
            }

            $priceListRows = PriceListItem::query()
                ->select([
                    'price_list_items.canonical_product_id',
                    'price_lists.partner_id as supplier_partner_id',
                    'price_list_items.unit_price',
                    'price_list_items.updated_at',
                    'price_list_items.id',
                ])
                ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
                ->whereIn('price_list_items.canonical_product_id', $canonicalProductIds)
                ->whereNotNull('price_lists.partner_id')
                ->whereNotNull('price_list_items.unit_price')
                ->orderByDesc('price_list_items.updated_at')
                ->orderByDesc('price_list_items.id')
                ->get();

            foreach ($priceListRows as $row) {
                $canonicalProductId = $row->canonical_product_id;
                $supplierId = $row->supplier_partner_id;
                if (! is_numeric($canonicalProductId) || ! is_numeric($supplierId) || ! is_numeric($row->unit_price)) {
                    continue;
                }

                $key = $this->quoteKey((int) $canonicalProductId, (int) $supplierId);
                if (isset($latestByKey[$key])) {
                    continue;
                }

                $latestByKey[$key] = (float) $row->unit_price;
            }
        }

        $byNorm = $this->priceListLatestByNormalizedNameAndSupplierMap();
        foreach ($supplyOrder->lines as $line) {
            $norm = $this->normalizeProductLabel($line->item_name);
            if ($norm === '' || ! isset($byNorm[$norm])) {
                continue;
            }
            foreach ($byNorm[$norm] as $supplierId => $unitPrice) {
                $key = $this->quoteKeyForLineLookup($line, $supplierId);
                if (isset($latestByKey[$key])) {
                    continue;
                }

                $latestByKey[$key] = $unitPrice;
            }
        }

        $this->mergeManualSupplierQuotesIntoLatestMap($supplyOrder, $latestByKey);

        return $latestByKey;
    }

    /**
     * Procurement-entered rows override inferred prices for the same line + supplier.
     *
     * @param  array<string, float>  $latestByKey
     */
    private function mergeManualSupplierQuotesIntoLatestMap(SupplyOrder $supplyOrder, array &$latestByKey): void
    {
        $lineIds = $supplyOrder->lines->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();
        if ($lineIds === []) {
            return;
        }

        $quotes = SupplyOrderLineSupplierQuote::query()
            ->whereIn('supply_order_line_id', $lineIds)
            ->whereNotNull('partner_id')
            ->get(['supply_order_line_id', 'partner_id', 'unit_price']);

        $linesById = $supplyOrder->lines->keyBy('id');
        foreach ($quotes as $quote) {
            $line = $linesById->get($quote->supply_order_line_id);
            if (! $line instanceof SupplyOrderLine) {
                continue;
            }
            if (! is_numeric($quote->unit_price)) {
                continue;
            }

            $key = $this->quoteKeyForLineLookup($line, (int) $quote->partner_id);
            $latestByKey[$key] = (float) $quote->unit_price;
        }
    }

    /**
     * @return array<string, array<int, float>>
     */
    private function priceListLatestByNormalizedNameAndSupplierMap(): array
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return [];
        }

        $cacheKey = (string) ($this->comparisonFormData['order_id'] ?? '');
        if ($this->priceListByNormCacheKey === $cacheKey && $this->priceListByNormMemo !== null) {
            return $this->priceListByNormMemo;
        }

        $items = PriceListItem::query()
            ->select([
                'price_list_items.product_name',
                'price_list_items.unit_price',
                'price_lists.partner_id as partner_id',
                'price_list_items.updated_at',
                'price_list_items.id',
            ])
            ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
            ->whereNotNull('price_list_items.product_name')
            ->whereNotNull('price_lists.partner_id')
            ->whereNotNull('price_list_items.unit_price')
            ->orderByDesc('price_list_items.updated_at')
            ->orderByDesc('price_list_items.id')
            ->get();

        $byNorm = [];
        foreach ($items as $row) {
            $norm = $this->normalizeProductLabel($row->product_name);
            if ($norm === '') {
                continue;
            }
            $partnerId = (int) $row->partner_id;
            if (! isset($byNorm[$norm][$partnerId])) {
                $byNorm[$norm][$partnerId] = (float) $row->unit_price;
            }
        }

        $this->priceListByNormCacheKey = $cacheKey;
        $this->priceListByNormMemo = $byNorm;

        return $byNorm;
    }

    /**
     * Collapse whitespace and compare case-insensitively (UTF-8) so supply-order {@see SupplyOrderLine::$item_name}
     * can match {@see PriceListItem::$product_name} without canonical mapping.
     */
    private function normalizeProductLabel(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        $collapsed = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * Quote map key: canonical SKU + supplier when mapped; otherwise per-line key so unmapped lines do not collide.
     */
    private function quoteKeyForLineLookup(SupplyOrderLine $line, int $supplierId): string
    {
        if (is_numeric($line->canonical_product_id)) {
            return $this->quoteKey((int) $line->canonical_product_id, $supplierId);
        }

        return 'line:'.$line->id.':'.$supplierId;
    }

    private function quoteKey(?int $canonicalProductId, int $supplierId): string
    {
        if (! is_numeric($canonicalProductId)) {
            return '0-'.$supplierId;
        }

        return ((int) $canonicalProductId).'-'.$supplierId;
    }

    private function resolvePricingTerm(Partner $supplier): string
    {
        $source = Str::upper(
            trim(
                implode(' ', array_filter([
                    $supplier->segment,
                    $supplier->reliability_note,
                    $supplier->name,
                ], fn ($value): bool => is_string($value) && trim($value) !== ''))
            )
        );

        foreach (['FOB', 'EXW', 'CIF', 'DDP', 'DAP'] as $term) {
            if (Str::contains($source, $term)) {
                return $term;
            }
        }

        return __('ops.supply_selection_analysis.compare.term_unknown');
    }

    public function table(Table $table): Table
    {
        $suppliers = $this->comparisonSuppliers();
        $supplierGroups = [];
        foreach ($suppliers as $supplier) {
            $supplierId = (int) $supplier->id;
            $supplierGroups[] = ColumnGroup::make(
                $this->supplierGroupLabel($supplier),
                [
                    TextColumn::make('matrix_'.$supplierId.'_picture')
                        ->label(new HtmlString('<div class="leading-tight text-center text-xs font-medium">'.e(__('ops.supply_selection_analysis.compare.subcol_picture')).'</div>'))
                        ->html()
                        ->alignment(Alignment::Center)
                        ->getStateUsing(fn (SupplyOrderLine $record): ?string => $this->comparisonRowsByLineId()[(int) $record->id]['image_url'] ?? null)
                        ->formatStateUsing(function (?string $state): string {
                            if ($state === null || $state === '') {
                                return '<span class="text-gray-400">/</span>';
                            }

                            return '<img src="'.e($state).'" alt="" class="mx-auto h-10 w-10 rounded object-cover" />';
                        })
                        ->summarize(Summarizer::make()->using(fn (): string => '')),
                    TextColumn::make('matrix_'.$supplierId.'_unit')
                        ->label(new HtmlString(
                            '<div class="leading-tight text-center">'
                            .'<div class="text-[11px] font-medium">'.e(__('ops.supply_selection_analysis.compare.subcol_unit_price')).'</div>'
                            .'<div class="text-[10px] text-gray-500 dark:text-gray-400">'.e(__('ops.supply_selection_analysis.compare.subcol_unit_price_currency')).'</div>'
                            .'</div>'
                        ))
                        ->alignment(Alignment::End)
                        ->getStateUsing(function (SupplyOrderLine $record) use ($supplierId): ?float {
                            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
                            if (! is_array($row)) {
                                return null;
                            }

                            $price = $row['prices'][$supplierId] ?? null;

                            return is_array($price) && is_numeric($price['unit_price'] ?? null)
                                ? (float) $price['unit_price']
                                : null;
                        })
                        ->formatStateUsing(function (?float $state): string {
                            if ($state === null) {
                                return '<span class="text-gray-400">/</span>';
                            }

                            return number_format($state, 4, '.', ',');
                        })
                        ->html()
                        ->summarize(Summarizer::make()->using(fn (): string => '')),
                    TextColumn::make('matrix_'.$supplierId.'_total')
                        ->label(new HtmlString('<div class="leading-tight text-center text-xs font-medium">'.e(__('ops.supply_selection_analysis.compare.subcol_total')).'</div>'))
                        ->alignment(Alignment::End)
                        ->html()
                        ->getStateUsing(function (SupplyOrderLine $record) use ($supplierId): ?float {
                            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
                            if (! is_array($row)) {
                                return null;
                            }

                            $price = $row['prices'][$supplierId] ?? null;

                            return is_array($price) && is_numeric($price['total'] ?? null)
                                ? (float) $price['total']
                                : null;
                        })
                        ->formatStateUsing(function (?float $state, SupplyOrderLine $record) use ($supplierId): string {
                            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
                            if (! is_array($row) || $state === null) {
                                return '<span class="text-gray-400">/</span>';
                            }

                            $isBest = ($row['best_supplier_id'] ?? null) === $supplierId;
                            $class = $isBest ? 'font-semibold text-success-600 dark:text-success-400' : 'font-medium';

                            return '<span class="'.$class.'">'.number_format($state, 2, '.', ',').'</span>';
                        })
                        ->summarize(
                            Summarizer::make()
                                ->using(fn (): float => (float) ($this->getSupplierGrandTotals()[$supplierId] ?? 0.0))
                                ->formatStateUsing(function ($state): string {
                                    $value = is_numeric($state) ? (float) $state : 0.0;

                                    return '<span class="font-semibold">'.number_format($value, 2, '.', ',').'</span>';
                                })
                                ->html()
                        ),
                ]
            )->wrapHeader();
        }

        return $table
            ->striped()
            ->query(
                SupplyOrderLine::query()->with([
                    'supplyOrder:id,supply_order_code',
                    'supplierPartner:id,name',
                    'canonicalProduct:id,sku,image_urls',
                ])
            )
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(function (Builder $query): void {
                $supplyOrderId = $this->comparisonFormData['order_id'] ?? null;
                if (is_numeric($supplyOrderId)) {
                    $query->where('supply_order_id', (int) $supplyOrderId);

                    return;
                }

                $query->whereRaw('1 = 0');
            })
            ->columns(array_merge([
                TextColumn::make('row_number')
                    ->label(__('ops.supply_selection_analysis.compare.col_no'))
                    ->rowIndex()
                    ->summarize(
                        Summarizer::make()
                            ->using(fn (): string => __('ops.supply_selection_analysis.compare.grand_total'))
                    ),
                TextColumn::make('item_name')
                    ->label(__('ops.supply_selection_analysis.compare.col_product_name'))
                    ->searchable()
                    ->wrap()
                    ->url(
                        fn (SupplyOrderLine $record): string => SupplyOrderLineSupplierQuotes::getUrl([
                            'supply_order_line' => $record->id,
                        ])
                    )
                    ->color('primary')
                    ->summarize(Summarizer::make()->using(fn (): string => '')),
                TextColumn::make('shortage_qty')
                    ->label(__('ops.supply_selection_analysis.compare.col_qty'))
                    ->formatStateUsing(
                        fn ($state): string => is_numeric($state)
                            ? number_format((float) $state, 0, ',', '.')
                            : '-'
                    )
                    ->summarize(Summarizer::make()->using(fn (): string => '')),
            ], $supplierGroups))
            ->paginated(false);
    }
}
