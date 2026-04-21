<?php

namespace App\Support\Ops;

use App\Models\Demand\PriceList;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reference price lists: date-valid + status for any consumer.
 * Supply selection uses {@see self::applyForSupplySelection} (procurement + both; sales-only excluded).
 */
final class PriceListVisibility
{
    public static function applyActiveAndDateValid(Builder $query): void
    {
        $today = now()->toDateString();

        $query->where('price_lists.status', 'active')
            ->where(function ($q) use ($today): void {
                $q->whereNull('price_lists.valid_from')
                    ->orWhereDate('price_lists.valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today): void {
                $q->whereNull('price_lists.valid_to')
                    ->orWhereDate('price_lists.valid_to', '>=', $today);
            });
    }

    /**
     * Lists used in {@see SupplySelectionAnalysis}: inbound procurement reference
     * (partner = supplier). Excludes sales-only lists.
     */
    public static function applyForSupplySelection(Builder $query): void
    {
        self::applyActiveAndDateValid($query);
        $query->where(function ($q): void {
            $q->whereNull('price_lists.list_scope')
                ->orWhere('price_lists.list_scope', PriceList::LIST_SCOPE_PROCUREMENT)
                ->orWhere('price_lists.list_scope', PriceList::LIST_SCOPE_BOTH);
        });
    }
}
