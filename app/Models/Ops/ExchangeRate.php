<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $quote_currency ISO 4217
 * @property string $base_currency ISO 4217 (usually VND)
 * @property string $rate decimal as string — base units per 1 quote unit
 * @property Carbon $effective_at
 * @property string|null $note
 */
class ExchangeRate extends Model
{
    protected $fillable = [
        'quote_currency',
        'base_currency',
        'rate',
        'effective_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'effective_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ExchangeRate $rate): void {
            $rate->quote_currency = strtoupper(trim($rate->quote_currency));
            $rate->base_currency = strtoupper(trim($rate->base_currency));
        });
    }

    /**
     * Latest row at or before {@see $asOf} for a quote→base pair (caller should ->first()).
     *
     * @return Builder<ExchangeRate>
     */
    public function scopeEffectiveAtOrBefore(Builder $query, string $quoteCurrency, string $baseCurrency, Carbon $asOf): Builder
    {
        return $query
            ->where('quote_currency', strtoupper(trim($quoteCurrency)))
            ->where('base_currency', strtoupper(trim($baseCurrency)))
            ->where('effective_at', '<=', $asOf)
            ->orderByDesc('effective_at');
    }

    public static function existsDuplicate(string $quoteCurrency, string $baseCurrency, Carbon $effectiveAt, ?int $exceptId = null): bool
    {
        $q = self::query()
            ->where('quote_currency', strtoupper(trim($quoteCurrency)))
            ->where('base_currency', strtoupper(trim($baseCurrency)))
            ->where('effective_at', $effectiveAt);
        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }
}
