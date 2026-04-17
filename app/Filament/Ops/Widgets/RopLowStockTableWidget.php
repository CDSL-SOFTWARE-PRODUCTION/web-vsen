<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Supply\InventoryLot;
use App\Support\Ops\FilamentAccess;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * C-INV-004: surface lots at or below ROP warn threshold (same heuristic as ops:rop-scan).
 *
 * Uses getTableQuery() + getTableColumns() so Filament's default table() wiring keeps an Eloquent model on the query.
 */
class RopLowStockTableWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return FilamentAccess::allowRoles(['Admin_PM', 'MuaHang', 'Kho', 'Sale']);
    }

    protected function getTableHeading(): ?string
    {
        return __('ops.widgets.rop.table_title');
    }

    protected function getTableQuery(): ?Builder
    {
        $threshold = (float) config('ops.rop_warn_below_qty', 10);

        return InventoryLot::query()
            ->where('available_qty', '<', $threshold)
            ->orderBy('warehouse_code')
            ->orderBy('item_name');
    }

    /**
     * @return array<Column>
     */
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('item_name')->label(__('ops.widgets.rop.col_item')),
            Tables\Columns\TextColumn::make('warehouse_code')->label(__('ops.widgets.rop.col_wh'))->placeholder('—'),
            Tables\Columns\TextColumn::make('available_qty')
                ->label(__('ops.widgets.rop.col_qty'))
                ->numeric(decimalPlaces: 3),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modelLabel(__('ops.widgets.rop.model_label'))
            ->pluralModelLabel(__('ops.widgets.rop.plural_model_label'))
            ->emptyStateHeading(__('ops.widgets.rop.empty'));
    }
}
