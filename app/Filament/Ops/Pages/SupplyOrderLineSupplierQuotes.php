<?php

namespace App\Filament\Ops\Pages;

use App\Models\Ops\Partner;
use App\Models\Supply\SupplyOrderLine;
use App\Models\Supply\SupplyOrderLineSupplierQuote;
use App\Support\Currency\CurrencyFormatter;
use App\Support\Currency\SupportedCurrencies;
use App\Support\Ops\FilamentAccess;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SupplyOrderLineSupplierQuotes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $routePath = '/supply-order-line-supplier-quotes/{supply_order_line}';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.ops.pages.supply-order-line-supplier-quotes';

    public SupplyOrderLine $supply_order_line;

    public function mount(SupplyOrderLine $supply_order_line): void
    {
        abort_unless(static::canAccess(), 403);

        $this->supply_order_line = $supply_order_line->load(['supplyOrder:id,supply_order_code', 'canonicalProduct:id,sku']);
    }

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public static function canAccess(): bool
    {
        if (FilamentAccess::isMasterDataSteward()) {
            return false;
        }

        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.supply_selection_analysis.line_quotes.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.supply_selection_analysis.line_quotes.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        $code = $this->supply_order_line->supplyOrder?->supply_order_code ?? '';

        return __('ops.supply_selection_analysis.line_quotes.subheading', [
            'name' => trim((string) $this->supply_order_line->item_name),
            'code' => $code,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('ops.supply_selection_analysis.line_quotes.back_to_matrix'))
                ->icon('heroicon-o-arrow-left')
                ->url(
                    SupplySelectionAnalysis::getUrl()
                        .'?order_id='.((int) $this->supply_order_line->supply_order_id)
                ),
        ];
    }

    public function table(Table $table): Table
    {
        $lineId = (int) $this->supply_order_line->id;

        return $table
            ->query(
                SupplyOrderLineSupplierQuote::query()
                    ->where('supply_order_line_id', $lineId)
                    ->with(['partner:id,name'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('partner.name')
                    ->label(__('ops.supply_selection_analysis.line_quotes.col_supplier'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('ops.supply_selection_analysis.line_quotes.col_unit_price'))
                    ->formatStateUsing(fn ($state, SupplyOrderLineSupplierQuote $record): string => is_numeric($state)
                        ? CurrencyFormatter::formatUnitPriceOrLegacy((float) $state, $record->currency_code)
                        : '')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('note')
                    ->label(__('ops.supply_selection_analysis.line_quotes.col_note'))
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('ops.supply_selection_analysis.line_quotes.col_updated_at'))
                    ->dateTime()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('ops.supply_selection_analysis.line_quotes.add_quote'))
                    ->model(SupplyOrderLineSupplierQuote::class)
                    ->form($this->quoteFormSchema())
                    ->mutateFormDataUsing(fn (array $data): array => array_merge($data, [
                        'supply_order_line_id' => $lineId,
                    ]))
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('ops.supply_selection_analysis.line_quotes.saved'))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn (Model $record): array => $this->quoteFormSchema($record instanceof SupplyOrderLineSupplierQuote ? $record : null))
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('ops.supply_selection_analysis.line_quotes.saved'))
                    ),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading(__('ops.supply_selection_analysis.line_quotes.empty_heading'))
            ->emptyStateDescription(__('ops.supply_selection_analysis.line_quotes.empty_desc'));
    }

    /**
     * @return array<int, Component>
     */
    private function quoteFormSchema(?SupplyOrderLineSupplierQuote $editing = null): array
    {
        $lineId = (int) $this->supply_order_line->id;

        return [
            Forms\Components\Select::make('partner_id')
                ->label(__('ops.supply_selection_analysis.line_quotes.field_supplier'))
                ->options(
                    fn (): array => Partner::query()
                        ->orderBy('name')
                        ->get(['id', 'name'])
                        ->mapWithKeys(fn (Partner $p): array => [$p->id => (string) $p->name])
                        ->all()
                )
                ->searchable()
                ->preload()
                ->required()
                ->rules([
                    Rule::unique('supply_order_line_supplier_quotes', 'partner_id')
                        ->where('supply_order_line_id', $lineId)
                        ->ignore($editing?->id),
                ])
                ->validationMessages([
                    'unique' => __('ops.supply_selection_analysis.line_quotes.duplicate_supplier'),
                ]),
            Forms\Components\Select::make('currency_code')
                ->label(__('ops.supply_selection_analysis.line_quotes.field_currency'))
                ->options(SupportedCurrencies::selectOptions())
                ->required()
                ->default('VND')
                ->rules([Rule::in(SupportedCurrencies::codes())]),
            Forms\Components\TextInput::make('unit_price')
                ->label(__('ops.supply_selection_analysis.line_quotes.field_unit_price'))
                ->numeric()
                ->required()
                ->minValue(0)
                ->step(0.0001),
            Forms\Components\Textarea::make('note')
                ->label(__('ops.supply_selection_analysis.line_quotes.field_note'))
                ->rows(2)
                ->maxLength(2000),
        ];
    }
}
