<?php

namespace App\Filament\Ops\Clusters\Supply\Pages;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\SupplyCluster;

use App\Filament\Ops\Widgets\SupplySelectionOverviewWidget;
use App\Models\Demand\PriceListItem;
use App\Models\Ops\Partner;
use App\Models\Supply\SupplyOrder;
use App\Models\Supply\SupplyOrderLine;
use App\Models\Supply\SupplyOrderLineSupplierQuote;
use App\Support\Currency\CurrencyConverter;
use App\Support\Currency\CurrencyFormatter;
use App\Support\Ops\FilamentAccess;
use App\Support\Ops\PriceListVisibility;
use App\Support\Supply\ProcurementQuantityFormatter;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter as TableFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplySelectionAnalysis extends Page implements HasForms, HasTable
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = SupplyCluster::class;
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
     * Latest unit price + currency per normalized product label and supplier, from price list items (newest row wins).
     *
     * @var array<string, array<int, array{unit_price: string, currency: string}>>|null
     */
    private ?array $priceListByNormMemo = null;

    /**
     * @var array<string, int|null>
     */
    private array $effectiveLeadTimeBySupplierMemo = [];

    private ?string $effectiveLeadTimeMemoCacheKey = null;

    /**
     * Resolved ISO 4217 per quote key (manual quotes, price lists, inferred legacy default for supply lines).
     *
     * @var array<string, string> quoteKey => currency code
     */
    private array $quoteCurrencyByQuoteKey = [];

    public function mount(): void
    {
        $orderId = request()->query('order_id');
        $id = is_numeric($orderId) ? (int) $orderId : null;
        // Set statePath explicitly so the matrix table query sees order_id on first paint (form fill alone can lag behind table registration).
        $this->comparisonFormData['order_id'] = $id;
        $this->form->fill([
            'order_id' => $id,
        ]);
    }

    /**
     * Wire round-trips: keep form + matrix in sync when opening ?order_id= from a link after partial hydration.
     */
    public function hydrate(): void
    {
        $fromQuery = request()->query('order_id');
        if (! is_numeric($fromQuery)) {
            return;
        }

        $id = (int) $fromQuery;
        $current = $this->comparisonFormData['order_id'] ?? null;
        if (! is_numeric($current)) {
            $this->comparisonFormData['order_id'] = $id;
        }
    }

    /**
     * Prefer form selection; fall back to {@see request()} `order_id` so deep links work before/without full form hydration.
     */
    private function resolvedSupplyOrderId(): ?int
    {
        $fromForm = $this->comparisonFormData['order_id'] ?? null;
        if (is_numeric($fromForm)) {
            return (int) $fromForm;
        }

        $fromQuery = request()->query('order_id');
        if (is_numeric($fromQuery)) {
            return (int) $fromQuery;
        }

        return null;
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
            Action::make('open_reference_price_lists')
                ->label(__('ops.supply_selection_analysis.actions.reference_price_lists'))
                ->tooltip(__('ops.supply_selection_analysis.actions.reference_price_lists_tooltip'))
                ->icon('heroicon-o-rectangle-stack')
                ->url(ProcurementReferencePriceListFlow::getUrl()),
            Action::make('refresh')
                ->label(__('ops.supply_selection_analysis.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn (): null => null),
            Action::make('export_matrix_csv')
                ->label(__('ops.supply_selection_analysis.export.matrix_csv'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): ?StreamedResponse {
                    $response = $this->makeSupplySelectionMatrixCsvDownload();
                    if ($response instanceof StreamedResponse) {
                        return $response;
                    }

                    Notification::make()
                        ->title(__('ops.supply_selection_analysis.export.empty'))
                        ->warning()
                        ->send();

                    return null;
                }),
        ];
    }

    /**
     * Stream a UTF-8 CSV of the current comparison matrix (visible suppliers, current table column order).
     */
    private function makeSupplySelectionMatrixCsvDownload(): ?StreamedResponse
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return null;
        }

        $suppliers = $this->orderedVisibleSuppliers();
        if ($suppliers->isEmpty()) {
            return null;
        }

        $rows = $this->getComparisonRows();
        if ($rows === []) {
            return null;
        }

        $supplierIds = $suppliers->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $supplierById = $suppliers->keyBy(fn (Partner $p): int => (int) $p->id);
        $grandTotals = $this->getSupplierGrandTotals();
        $baseCurrency = CurrencyConverter::baseCurrency();

        $code = (string) ($supplyOrder->supply_order_code ?? '');
        $slug = Str::slug($code !== '' ? $code : 'order-'.$supplyOrder->id);
        $filename = 'supply-selection-'.$slug.'-'.now()->format('Y-m-d_His').'.csv';

        $headerRow = [
            __('ops.supply_selection_analysis.compare.export_col_line_no'),
            __('ops.supply_selection_analysis.compare.col_order_line'),
            __('ops.supply_selection_analysis.compare.col_sku'),
            __('ops.supply_selection_analysis.compare.col_qty'),
            __('ops.supply_selection_analysis.compare.export_col_fx_incomplete'),
            __('ops.supply_selection_analysis.compare.export_col_best_supplier_id'),
            __('ops.supply_selection_analysis.compare.export_col_best_supplier'),
        ];
        foreach ($suppliers as $supplier) {
            $name = (string) $supplier->name;
            $headerRow[] = $name.' — '.__('ops.supply_selection_analysis.compare.subcol_unit_price');
            $headerRow[] = $name.' — '.__('ops.supply_selection_analysis.compare.export_col_currency');
            $headerRow[] = $name.' — '.__('ops.supply_selection_analysis.compare.subcol_total');
        }

        return response()->streamDownload(function () use (
            $rows,
            $supplierIds,
            $supplierById,
            $grandTotals,
            $baseCurrency,
            $headerRow
        ): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headerRow);

            $lineNo = 0;
            foreach ($rows as $row) {
                $lineNo++;
                $bestId = isset($row['best_supplier_id']) && is_numeric($row['best_supplier_id'])
                    ? (int) $row['best_supplier_id']
                    : null;
                $bestName = $bestId !== null
                    ? (string) ($supplierById->get($bestId)?->name ?? '')
                    : '';

                $csvRow = [
                    (string) $lineNo,
                    (string) ($row['order_line'] ?? ''),
                    (string) ($row['canonical_sku'] ?? ''),
                    isset($row['qty']) && is_numeric($row['qty'])
                        ? ProcurementQuantityFormatter::formatCsv($row['qty'])
                        : '',
                    (($row['currency_conversion_incomplete'] ?? false) === true) ? '1' : '0',
                    $bestId !== null ? (string) $bestId : '',
                    $bestName,
                ];

                $prices = is_array($row['prices'] ?? null) ? $row['prices'] : [];
                foreach ($supplierIds as $supplierId) {
                    $p = $prices[$supplierId] ?? $prices[(string) $supplierId] ?? null;
                    if (! is_array($p)) {
                        $csvRow[] = '';
                        $csvRow[] = '';
                        $csvRow[] = '';

                        continue;
                    }
                    $unit = isset($p['unit_price']) && is_numeric($p['unit_price'])
                        ? CurrencyFormatter::normalizeAmountString($p['unit_price'])
                        : null;
                    $total = isset($p['total']) && is_numeric($p['total'])
                        ? CurrencyFormatter::normalizeAmountString($p['total'])
                        : null;
                    $currency = isset($p['currency_code']) && is_string($p['currency_code']) && trim($p['currency_code']) !== ''
                        ? strtoupper(trim($p['currency_code']))
                        : (isset($p['effective_currency']) && is_string($p['effective_currency']) ? $p['effective_currency'] : '');
                    $csvRow[] = $this->csvNumericCell($unit);
                    $csvRow[] = $currency;
                    $csvRow[] = $this->csvNumericCell($total);
                }

                fputcsv($handle, $csvRow);
            }

            $footerRow = [
                __('ops.supply_selection_analysis.compare.grand_total').' ('.$baseCurrency.')',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            foreach ($supplierIds as $supplierId) {
                $t = $grandTotals[$supplierId] ?? null;
                $footerRow[] = '';
                $footerRow[] = '';
                if ($t === null) {
                    $footerRow[] = __('ops.supply_selection_analysis.compare.grand_total_unavailable');
                } else {
                    $footerRow[] = $this->csvNumericCell((float) $t);
                }
            }

            fputcsv($handle, $footerRow);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function csvNumericCell(float|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            $n = CurrencyFormatter::normalizeAmountString($value);

            return $n ?? '';
        }

        return rtrim(rtrim(number_format($value, 8, '.', ''), '0'), '.');
    }

    /**
     * Layer 2 pilot: matrix highlight / hidden suppliers / column order survive page reload (session key scoped to this page class).
     */
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
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
     *   line_id:int,
     *   order_line:string,
     *   canonical_sku:string,
     *   qty:string,
     *   prices:array<int,array{
     *     unit_price:string|null,
     *     total:string|null,
     *     currency_code:string|null,
     *     effective_currency:string
     *   }>,
     *   best_supplier_id:int|null,
     *   currency_conversion_incomplete:bool
     * }>
     */
    public function getComparisonRows(): array
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return [];
        }

        $suppliers = $this->visibleComparisonSuppliers();
        if ($suppliers->isEmpty()) {
            return [];
        }

        $supplyOrder->loadMissing('lines.canonicalProduct');
        $latestQuotes = $this->latestQuotesByProductAndSupplier($supplyOrder);
        $supplierIds = $suppliers->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $rows = [];
        foreach ($supplyOrder->lines as $line) {
            // Line totals use raw shortage_qty from DB (may be fractional). UI shows rounded integers only.
            $qtyStr = CurrencyFormatter::normalizeAmountString($line->shortage_qty) ?? '0';
            $priceBySupplier = [];
            $conversionIncomplete = false;

            foreach ($supplierIds as $supplierId) {
                $lineQuoteKey = $this->lineSupplierQuoteKey((int) $line->id, $supplierId);
                $unitPrice = $latestQuotes[$lineQuoteKey] ?? null;
                $currencyCode = $this->quoteCurrencyByQuoteKey[$lineQuoteKey] ?? null;

                if ($unitPrice === null) {
                    $quoteKey = $this->quoteKeyForLineLookup($line, $supplierId);
                    $unitPrice = $latestQuotes[$quoteKey] ?? null;
                    $currencyCode = $this->quoteCurrencyByQuoteKey[$quoteKey] ?? null;
                }

                $effectiveCurrency = $this->resolveEffectiveCurrency($currencyCode);
                $lineTotal = $unitPrice === null
                    ? null
                    : CurrencyFormatter::roundLineTotal(
                        CurrencyFormatter::multiplyUnitByQty($unitPrice, $qtyStr),
                        $effectiveCurrency
                    );
                $priceBySupplier[$supplierId] = [
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                    'currency_code' => $currencyCode,
                    'effective_currency' => $effectiveCurrency,
                ];
            }

            $bestSupplierId = null;
            $bestInBase = null;
            foreach ($supplierIds as $supplierId) {
                $p = $priceBySupplier[$supplierId];
                if ($p['total'] === null) {
                    continue;
                }
                $inBase = CurrencyConverter::toBase(
                    (float) (CurrencyFormatter::normalizeAmountString($p['total']) ?? '0'),
                    $p['effective_currency']
                );
                if ($inBase === null) {
                    $conversionIncomplete = true;

                    continue;
                }
                if ($bestInBase === null || $inBase < $bestInBase) {
                    $bestInBase = $inBase;
                    $bestSupplierId = $supplierId;
                }
            }

            if ($conversionIncomplete) {
                $bestSupplierId = null;
            }

            $rows[] = [
                'line_id' => (int) $line->id,
                'order_line' => trim((string) $line->item_name),
                'canonical_sku' => (string) ($line->canonicalProduct?->sku ?? ''),
                'qty' => $qtyStr,
                'prices' => $priceBySupplier,
                'best_supplier_id' => $bestSupplierId,
                'currency_conversion_incomplete' => $conversionIncomplete,
            ];
        }

        return $rows;
    }

    private function resolveEffectiveCurrency(?string $code): string
    {
        if (is_string($code) && trim($code) !== '') {
            return strtoupper(trim($code));
        }

        return CurrencyConverter::legacyDefault();
    }

    /**
     * True when any matrix row is missing a configured FX rate (best highlight and column totals are suppressed).
     */
    public function hasComparisonCurrencyGap(): bool
    {
        foreach ($this->getComparisonRows() as $row) {
            if (($row['currency_conversion_incomplete'] ?? false) === true) {
                return true;
            }
        }

        foreach ($this->getSupplierGrandTotals() as $total) {
            if ($total === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grand totals in {@see CurrencyConverter::baseCurrency()} (sum of per-line conversions). Null when any line for that supplier lacks an FX rate.
     *
     * @return array<int, float|null>
     */
    public function getSupplierGrandTotals(): array
    {
        $rows = $this->getComparisonRows();
        $grandTotals = [];
        foreach ($this->visibleComparisonSuppliers() as $supplier) {
            $grandTotals[(int) $supplier->id] = 0.0;
        }

        foreach ($rows as $row) {
            foreach ($grandTotals as $supplierId => $_) {
                $p = $row['prices'][$supplierId] ?? null;
                if (! is_array($p) || $p['total'] === null) {
                    continue;
                }
                $base = CurrencyConverter::toBase((float) (CurrencyFormatter::normalizeAmountString($p['total']) ?? '0'), $p['effective_currency']);
                if ($base === null) {
                    $grandTotals[$supplierId] = null;
                } elseif ($grandTotals[$supplierId] !== null) {
                    $grandTotals[$supplierId] += $base;
                }
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

            $fromPriceListsQuery = PriceListItem::query()
                ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
                ->whereIn('price_list_items.canonical_product_id', $canonicalProductIds)
                ->whereNotNull('price_lists.partner_id');
            PriceListVisibility::applyForSupplySelection($fromPriceListsQuery);
            $fromPriceLists = $fromPriceListsQuery->distinct()
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
            ->get(['id', 'name', 'segment', 'reliability_note', 'lead_time_days']);
    }

    /**
     * Suppliers shown as matrix columns after applying table "hide" filter.
     *
     * @return Collection<int, Partner>
     */
    public function visibleComparisonSuppliers(): Collection
    {
        $hidden = $this->hiddenSupplierIds();
        $all = $this->comparisonSuppliers();
        if ($hidden === []) {
            return $all;
        }

        return $all
            ->filter(fn (Partner $p): bool => ! in_array((int) $p->id, $hidden, true))
            ->values();
    }

    /**
     * Visible suppliers re-ordered by table "sort columns" filter.
     *
     * @return Collection<int, Partner>
     */
    private function orderedVisibleSuppliers(): Collection
    {
        $suppliers = $this->visibleComparisonSuppliers();
        if ($suppliers->isEmpty()) {
            return $suppliers;
        }

        $order = $this->tableFilterValue('supplier_order', 'value');
        if (! is_string($order) || $order === '') {
            $order = 'name';
        }

        return match ($order) {
            'total_asc' => $suppliers->sortBy(function (Partner $s): float {
                $totals = $this->getSupplierGrandTotals();
                $t = $totals[(int) $s->id] ?? null;

                return $t === null ? PHP_FLOAT_MAX : $t;
            })->values(),
            'lead_time_asc' => $suppliers->sortBy(function (Partner $s): int {
                $d = $this->effectiveLeadTimeDaysForSupplier($s);

                return $d === null ? PHP_INT_MAX : $d;
            })->values(),
            default => $suppliers->sortBy('name')->values(),
        };
    }

    /** Reads a single key from committed Filament table filter state. */
    private function tableFilterValue(string $name, string $key = 'value'): mixed
    {
        $block = $this->getTableFilterState($name);
        if (! is_array($block)) {
            return null;
        }

        return $block[$key] ?? null;
    }

    /**
     * @return list<int>
     */
    private function hiddenSupplierIds(): array
    {
        $ids = [];

        $legacy = $this->getTableFilterState('hidden_suppliers');
        if (is_array($legacy) && isset($legacy['values']) && is_array($legacy['values'])) {
            $ids = $legacy['values'];
        } else {
            $visibility = $this->getTableFilterState('supplier_visibility');
            if (is_array($visibility) && isset($visibility['hidden_ids']) && is_array($visibility['hidden_ids'])) {
                $ids = $visibility['hidden_ids'];
            }
        }

        if ($ids === []) {
            return [];
        }

        $normalized = [];
        foreach ($ids as $v) {
            if (is_int($v) || is_float($v)) {
                $normalized[] = (int) $v;

                continue;
            }
            if (is_string($v) && ctype_digit($v)) {
                $normalized[] = (int) $v;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Whole-column highlight: lowest converted grand total, or lowest effective lead time
     * ({@see Partner::$lead_time_days} vs. {@see PriceListItem::$lead_time_days} on active lists in date).
     */
    public function matrixHighlightedSupplierId(): ?int
    {
        return match ($this->matrixHighlightMode()) {
            'column_lowest_total' => $this->supplierIdWithCheapestGrandTotal(),
            'fastest_delivery' => $this->supplierIdWithFastestLeadTime(),
            default => null,
        };
    }

    private function matrixHighlightMode(): string
    {
        $mode = $this->tableFilterValue('matrix_highlight', 'value');
        if (! is_string($mode) || $mode === '') {
            return 'line_best';
        }

        return in_array($mode, ['none', 'line_best', 'column_lowest_total', 'fastest_delivery'], true)
            ? $mode
            : 'line_best';
    }

    private function supplierIdWithCheapestGrandTotal(): ?int
    {
        $totals = $this->getSupplierGrandTotals();
        $bestId = null;
        $bestVal = null;
        foreach ($this->visibleComparisonSuppliers() as $supplier) {
            $id = (int) $supplier->id;
            $t = $totals[$id] ?? null;
            if ($t === null) {
                continue;
            }
            if ($bestVal === null || $t < $bestVal) {
                $bestVal = $t;
                $bestId = $id;
            }
        }

        return $bestId;
    }

    private function supplierIdWithFastestLeadTime(): ?int
    {
        $bestId = null;
        $bestDays = null;
        foreach ($this->visibleComparisonSuppliers() as $supplier) {
            $d = $this->effectiveLeadTimeDaysForSupplier($supplier);
            if ($d === null) {
                continue;
            }
            if ($bestDays === null || $d < $bestDays) {
                $bestDays = $d;
                $bestId = (int) $supplier->id;
            }
        }

        return $bestId;
    }

    /**
     * Cache key: selected order + canonical SKUs on that order (lead inference uses SKU-scoped price lines).
     */
    private function effectiveLeadTimeMemoKey(): string
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return '';
        }

        $canonicalCsv = $supplyOrder->lines
            ->pluck('canonical_product_id')
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->implode(',');

        return (string) ($this->resolvedSupplyOrderId() ?? '').'|c:'.$canonicalCsv;
    }

    /**
     * Shortest delivery signal for matrix sorting / highlights: min of partner lead and the minimum
     * {@see PriceListItem::$lead_time_days} on active, date-valid lists for this partner and the order's canonical SKUs.
     */
    private function effectiveLeadTimeDaysForSupplier(Partner $supplier): ?int
    {
        $cacheKey = $this->effectiveLeadTimeMemoKey();
        if ($this->effectiveLeadTimeMemoCacheKey !== $cacheKey) {
            $this->effectiveLeadTimeBySupplierMemo = [];
            $this->effectiveLeadTimeMemoCacheKey = $cacheKey;
        }

        $memKey = (string) $supplier->id;
        if (array_key_exists($memKey, $this->effectiveLeadTimeBySupplierMemo)) {
            return $this->effectiveLeadTimeBySupplierMemo[$memKey];
        }

        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            $this->effectiveLeadTimeBySupplierMemo[$memKey] = null;

            return null;
        }

        $canonicalProductIds = $supplyOrder->lines
            ->pluck('canonical_product_id')
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $candidates = [];
        if (is_numeric($supplier->lead_time_days)) {
            $d = (int) $supplier->lead_time_days;
            if ($d >= 0) {
                $candidates[] = $d;
            }
        }

        if ($canonicalProductIds !== []) {
            $q = PriceListItem::query()
                ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
                ->where('price_lists.partner_id', $supplier->id)
                ->whereIn('price_list_items.canonical_product_id', $canonicalProductIds)
                ->whereNotNull('price_list_items.lead_time_days');
            PriceListVisibility::applyForSupplySelection($q);
            $rawMin = $q->min('price_list_items.lead_time_days');
            if (is_numeric($rawMin)) {
                $m = (int) $rawMin;
                if ($m >= 0) {
                    $candidates[] = $m;
                }
            }
        }

        if ($candidates === []) {
            $this->effectiveLeadTimeBySupplierMemo[$memKey] = null;

            return null;
        }

        $result = min($candidates);
        $this->effectiveLeadTimeBySupplierMemo[$memKey] = $result;

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function matrixDataCellAttributes(int $supplierId, SupplyOrderLine $record): array
    {
        $mode = $this->matrixHighlightMode();
        $classes = [];

        if ($mode === 'column_lowest_total' && $this->matrixHighlightedSupplierId() === $supplierId) {
            $classes[] = '!bg-primary-200/95 dark:!bg-primary-950/75 ring-2 ring-inset ring-primary-600/45 dark:ring-primary-400/35';
        }
        if ($mode === 'fastest_delivery' && $this->matrixHighlightedSupplierId() === $supplierId) {
            $classes[] = '!bg-primary-200/95 dark:!bg-primary-950/75 ring-2 ring-inset ring-primary-600/45 dark:ring-primary-400/35';
        }
        if ($mode === 'line_best') {
            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
            if (is_array($row) && (int) ($row['best_supplier_id'] ?? 0) === $supplierId) {
                $classes[] = '!bg-emerald-200/95 dark:!bg-emerald-950/70 ring-2 ring-inset ring-emerald-600/50 dark:ring-emerald-400/40';
            }
        }

        return $classes !== [] ? ['class' => implode(' ', $classes)] : [];
    }

    public function selectedSupplyOrderCode(): ?string
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return null;
        }

        return (string) $supplyOrder->supply_order_code;
    }

    private function supplierGroupLabel(Partner $supplier, bool $highlightWholeColumn): HtmlString
    {
        $term = $this->resolvePricingTerm($supplier);
        $lead = $this->effectiveLeadTimeDaysForSupplier($supplier);
        $subParts = [$term];
        if ($lead !== null) {
            $subParts[] = __('ops.supply_selection_analysis.compare.lead_days_short', ['days' => $lead]);
        }
        $sub = implode(' · ', $subParts);
        $wrap = $highlightWholeColumn
            ? 'rounded-md px-1 py-1 ring-2 ring-primary-500/90 bg-primary-50/90 dark:bg-primary-950/50'
            : '';

        return new HtmlString(
            '<div class="leading-tight text-center '.$wrap.'"><div class="font-semibold">'.e((string) $supplier->name).'</div><div class="text-xs text-gray-500 dark:text-gray-400">'.e($sub).'</div></div>'
        );
    }

    /**
     * @return array<int, array{
     *   line_id:int,
     *   order_line:string,
     *   canonical_sku:string,
     *   qty:string,
     *   prices:array<int,array{unit_price:string|null,total:string|null,currency_code:string|null,effective_currency:string}>,
     *   best_supplier_id:int|null
     * }>
     */
    private function comparisonRowsByLineId(): array
    {
        $cacheKey = (string) ($this->resolvedSupplyOrderId() ?? '')
            .'|h:'.implode(',', $this->hiddenSupplierIds());
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
        $supplyOrderId = $this->resolvedSupplyOrderId();
        if ($supplyOrderId === null) {
            return null;
        }

        return SupplyOrder::query()->find($supplyOrderId);
    }

    /**
     * @return array<string, string>
     */
    private function latestQuotesByProductAndSupplier(SupplyOrder $supplyOrder): array
    {
        $this->quoteCurrencyByQuoteKey = [];

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

                $normalized = CurrencyFormatter::normalizeAmountString($quote->planned_unit_price);
                if ($normalized === null) {
                    continue;
                }
                $latestByKey[$key] = $normalized;
                $this->quoteCurrencyByQuoteKey[$key] = CurrencyConverter::legacyDefault();
            }

            $priceListRowsQuery = PriceListItem::query()
                ->select([
                    'price_list_items.canonical_product_id',
                    'price_lists.partner_id as supplier_partner_id',
                    'price_list_items.unit_price',
                    'price_list_items.currency',
                    'price_list_items.updated_at',
                    'price_list_items.id',
                ])
                ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
                ->whereIn('price_list_items.canonical_product_id', $canonicalProductIds)
                ->whereNotNull('price_lists.partner_id')
                ->whereNotNull('price_list_items.unit_price');
            PriceListVisibility::applyForSupplySelection($priceListRowsQuery);
            $priceListRows = $priceListRowsQuery
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

                $normalizedPl = CurrencyFormatter::normalizeAmountString($row->unit_price);
                if ($normalizedPl === null) {
                    continue;
                }
                $latestByKey[$key] = $normalizedPl;
                $currency = is_string($row->currency) && trim($row->currency) !== ''
                    ? strtoupper(trim($row->currency))
                    : CurrencyConverter::legacyDefault();
                $this->quoteCurrencyByQuoteKey[$key] = $currency;
            }
        }

        $byNorm = $this->priceListLatestByNormalizedNameAndSupplierMap();
        foreach ($supplyOrder->lines as $line) {
            $norm = $this->normalizeProductLabel($line->item_name);
            if ($norm === '' || ! isset($byNorm[$norm])) {
                continue;
            }
            foreach ($byNorm[$norm] as $supplierId => $payload) {
                $key = $this->quoteKeyForLineLookup($line, $supplierId);
                if (isset($latestByKey[$key])) {
                    continue;
                }

                $latestByKey[$key] = $payload['unit_price'];
                $this->quoteCurrencyByQuoteKey[$key] = $payload['currency'];
            }
        }

        $this->mergeManualSupplierQuotesIntoLatestMap($supplyOrder, $latestByKey);

        return $latestByKey;
    }

    /**
     * Procurement-entered rows override inferred prices for the same line + supplier.
     *
     * @param  array<string, string>  $latestByKey
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
            ->orderBy('updated_at')
            ->orderBy('id')
            ->get(['supply_order_line_id', 'partner_id', 'unit_price', 'currency_code']);

        $linesById = $supplyOrder->lines->keyBy('id');
        foreach ($quotes as $quote) {
            $line = $linesById->get($quote->supply_order_line_id);
            if (! $line instanceof SupplyOrderLine) {
                continue;
            }
            if (! is_numeric($quote->unit_price)) {
                continue;
            }

            $partnerId = (int) $quote->partner_id;
            $lineKey = $this->lineSupplierQuoteKey((int) $line->id, $partnerId);
            $normalizedMq = CurrencyFormatter::normalizeAmountString($quote->unit_price);
            if ($normalizedMq === null) {
                continue;
            }
            $latestByKey[$lineKey] = $normalizedMq;
            $code = $quote->currency_code ?? null;
            $this->quoteCurrencyByQuoteKey[$lineKey] = is_string($code) && $code !== ''
                ? strtoupper($code)
                : CurrencyConverter::legacyDefault();
        }
    }

    /**
     * Per-line manual quotes from {@see SupplyOrderLineSupplierQuote} — stable key regardless of canonical mapping.
     */
    private function lineSupplierQuoteKey(int $supplyOrderLineId, int $supplierPartnerId): string
    {
        return 'line:'.$supplyOrderLineId.':'.$supplierPartnerId;
    }

    /**
     * @return array<string, array<int, array{unit_price: string, currency: string}>>
     */
    private function priceListLatestByNormalizedNameAndSupplierMap(): array
    {
        $supplyOrder = $this->selectedSupplyOrder();
        if (! $supplyOrder instanceof SupplyOrder) {
            return [];
        }

        $cacheKey = (string) ($this->resolvedSupplyOrderId() ?? '');
        if ($this->priceListByNormCacheKey === $cacheKey && $this->priceListByNormMemo !== null) {
            return $this->priceListByNormMemo;
        }

        $itemsQuery = PriceListItem::query()
            ->select([
                'price_list_items.product_name',
                'price_list_items.unit_price',
                'price_list_items.currency',
                'price_lists.partner_id as partner_id',
                'price_list_items.updated_at',
                'price_list_items.id',
            ])
            ->join('price_lists', 'price_list_items.price_list_id', '=', 'price_lists.id')
            ->whereNotNull('price_list_items.product_name')
            ->whereNotNull('price_lists.partner_id')
            ->whereNotNull('price_list_items.unit_price');
        PriceListVisibility::applyForSupplySelection($itemsQuery);
        $items = $itemsQuery
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
                $currency = is_string($row->currency) && trim($row->currency) !== ''
                    ? strtoupper(trim($row->currency))
                    : CurrencyConverter::legacyDefault();
                $normalizedName = CurrencyFormatter::normalizeAmountString($row->unit_price);
                if ($normalizedName === null) {
                    continue;
                }
                $byNorm[$norm][$partnerId] = [
                    'unit_price' => $normalizedName,
                    'currency' => $currency,
                ];
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
        $suppliers = $this->orderedVisibleSuppliers();
        $supplierGroups = [];
        $columnModes = ['column_lowest_total', 'fastest_delivery'];
        foreach ($suppliers as $supplier) {
            $supplierId = (int) $supplier->id;
            $highlightWholeColumn = in_array($this->matrixHighlightMode(), $columnModes, true)
                && $this->matrixHighlightedSupplierId() === $supplierId;
            $supplierGroups[] = ColumnGroup::make(
                $this->supplierGroupLabel($supplier, $highlightWholeColumn),
                [
                    TextColumn::make('matrix_'.$supplierId.'_unit')
                        ->label(new HtmlString(
                            '<div class="leading-tight text-center">'
                            .'<div class="text-[11px] font-medium">'.e(__('ops.supply_selection_analysis.compare.subcol_unit_price')).'</div>'
                            .'<div class="text-[10px] text-gray-500 dark:text-gray-400">'.e(__('ops.supply_selection_analysis.compare.subcol_unit_price_currency')).'</div>'
                            .'</div>'
                        ))
                        ->alignment(Alignment::End)
                        ->extraCellAttributes(fn (SupplyOrderLine $record): array => $this->matrixDataCellAttributes($supplierId, $record))
                        ->getStateUsing(function (SupplyOrderLine $record) use ($supplierId): string {
                            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
                            if (! is_array($row)) {
                                return '';
                            }

                            $prices = $row['prices'] ?? [];
                            $price = $prices[$supplierId] ?? $prices[(string) $supplierId] ?? null;
                            if (! is_array($price) || ! is_numeric($price['unit_price'] ?? null)) {
                                return '';
                            }

                            $effective = is_string($price['effective_currency'] ?? null) && $price['effective_currency'] !== ''
                                ? $price['effective_currency']
                                : CurrencyConverter::legacyDefault();

                            $text = e(CurrencyFormatter::formatUnitPrice($price['unit_price'], $effective));
                            $mode = $this->matrixHighlightMode();
                            $isLineBest = ($row['best_supplier_id'] ?? null) === $supplierId;
                            if ($mode === 'line_best' && $isLineBest) {
                                return '<span class="font-semibold text-gray-900 dark:text-gray-50">'.$text.'</span>';
                            }

                            return $text;
                        })
                        ->html()
                        ->summarize(Summarizer::make()->using(fn (): string => '')),
                    TextColumn::make('matrix_'.$supplierId.'_total')
                        ->label(new HtmlString('<div class="leading-tight text-center text-xs font-medium">'.e(__('ops.supply_selection_analysis.compare.subcol_total')).'</div>'))
                        ->alignment(Alignment::End)
                        ->html()
                        ->extraCellAttributes(fn (SupplyOrderLine $record): array => $this->matrixDataCellAttributes($supplierId, $record))
                        ->getStateUsing(function (SupplyOrderLine $record) use ($supplierId): string {
                            $row = $this->comparisonRowsByLineId()[(int) $record->id] ?? null;
                            if (! is_array($row)) {
                                return '';
                            }

                            $prices = $row['prices'] ?? [];
                            $price = $prices[$supplierId] ?? $prices[(string) $supplierId] ?? null;
                            if (! is_array($price) || ! is_numeric($price['total'] ?? null)) {
                                return '';
                            }

                            $mode = $this->matrixHighlightMode();
                            $isLineBest = ($row['best_supplier_id'] ?? null) === $supplierId;
                            $showRowBestAccent = $mode === 'line_best' && $isLineBest;
                            $class = $showRowBestAccent ? 'font-semibold text-gray-900 dark:text-gray-50' : 'font-medium';
                            $effective = is_string($price['effective_currency'] ?? null) && $price['effective_currency'] !== ''
                                ? $price['effective_currency']
                                : CurrencyConverter::legacyDefault();
                            $formatted = CurrencyFormatter::formatUnitPrice($price['total'], $effective);

                            return '<span class="'.$class.'">'.e($formatted).'</span>';
                        })
                        ->summarize(
                            Summarizer::make()
                                ->using(fn (): ?float => $this->getSupplierGrandTotals()[$supplierId] ?? null)
                                ->formatStateUsing(function ($state) use ($supplierId): string {

                                    $highlightColumnFooter = in_array($this->matrixHighlightMode(), ['column_lowest_total', 'fastest_delivery'], true)
                                        && $this->matrixHighlightedSupplierId() === $supplierId;
                                    $inner = '';
                                    if ($state === null) {
                                        $inner = '<span class="text-warning-600">'.e(__('ops.supply_selection_analysis.compare.grand_total_unavailable')).'</span>';
                                    } else {
                                        $value = is_numeric($state) ? (float) $state : 0.0;
                                        $inner = '<span class="font-semibold">'.e(CurrencyFormatter::formatUnitPrice($value, CurrencyConverter::baseCurrency())).'</span>';
                                    }
                                    if ($highlightColumnFooter) {
                                        return '<span class="inline-block w-full rounded-sm px-2 py-1.5 ring-2 ring-inset ring-primary-600/45 bg-primary-200/95 dark:bg-primary-950/75 dark:ring-primary-400/35">'.$inner.'</span>';
                                    }

                                    return $inner;
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
                    'canonicalProduct:id,sku',
                ])
            )
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(function (Builder $query): void {
                $supplyOrderId = $this->resolvedSupplyOrderId();
                if ($supplyOrderId !== null) {
                    $query->where('supply_order_id', $supplyOrderId);

                    return;
                }

                $query->whereRaw('1 = 0');
            })
            ->deferFilters(false)
            ->filters([
                SelectFilter::make('matrix_highlight')
                    ->label(__('ops.supply_selection_analysis.compare.matrix_highlight'))
                    ->options([
                        'line_best' => __('ops.supply_selection_analysis.compare.matrix_highlight_line_best'),
                        'none' => __('ops.supply_selection_analysis.compare.matrix_highlight_none'),
                        'column_lowest_total' => __('ops.supply_selection_analysis.compare.matrix_highlight_column_lowest'),
                        'fastest_delivery' => __('ops.supply_selection_analysis.compare.matrix_highlight_fastest'),
                    ])
                    ->default('line_best')
                    ->static(),
                TableFilter::make('supplier_visibility')
                    ->label(__('ops.supply_selection_analysis.compare.hide_suppliers'))
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\CheckboxList::make('hidden_ids')
                            ->label(__('ops.supply_selection_analysis.compare.hide_suppliers'))
                            ->helperText(__('ops.supply_selection_analysis.compare.hide_suppliers_hint'))
                            ->options(function (): array {
                                return $this->comparisonSuppliers()
                                    ->mapWithKeys(fn (Partner $p): array => [(string) $p->id => (string) $p->name])
                                    ->all();
                            })
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->searchable()
                            ->live(),
                    ]),
                SelectFilter::make('supplier_order')
                    ->label(__('ops.supply_selection_analysis.compare.supplier_order'))
                    ->options([
                        'name' => __('ops.supply_selection_analysis.compare.supplier_order_name'),
                        'total_asc' => __('ops.supply_selection_analysis.compare.supplier_order_total_asc'),
                        'lead_time_asc' => __('ops.supply_selection_analysis.compare.supplier_order_lead_asc'),
                    ])
                    ->default('name')
                    ->static(),
            ])
            ->filtersFormColumns(3)
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
                    ->tooltip(__('ops.supply_selection_analysis.compare.col_qty_tooltip'))
                    ->formatStateUsing(
                        fn ($state): string => ProcurementQuantityFormatter::formatDisplay($state)
                    )
                    ->summarize(Summarizer::make()->using(fn (): string => '')),
            ], $supplierGroups))
            ->paginated(false);
    }
}
