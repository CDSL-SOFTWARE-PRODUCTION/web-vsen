<?php

namespace App\Support\Currency;

use App\Models\Ops\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Converts monetary amounts into the configured base currency.
 * Rates: latest {@see ExchangeRate} row with effective_at <= asOf, else config/currency.php rates_to_base.
 */
final class CurrencyConverter
{
    /**
     * @var array<string, float|null>
     */
    private static array $resolvedRateCache = [];

    public static function baseCurrency(): string
    {
        return (string) config('currency.base', 'VND');
    }

    public static function legacyDefault(): string
    {
        return (string) config('currency.legacy_default', 'VND');
    }

    /**
     * Base units per 1 unit of {@see $currencyCode} (e.g. VND per 1 USD), at {@see $asOf}.
     */
    public static function resolveRateToBase(string $currencyCode, ?Carbon $asOf = null): ?float
    {
        $asOf ??= Carbon::now();
        $code = strtoupper(trim($currencyCode));
        $base = self::baseCurrency();

        if ($code === $base) {
            return 1.0;
        }

        $cacheKey = $code.'|'.$base.'|'.$asOf->format('Y-m-d H:i');
        if (array_key_exists($cacheKey, self::$resolvedRateCache)) {
            return self::$resolvedRateCache[$cacheKey];
        }

        $row = null;
        if (Schema::hasTable('exchange_rates')) {
            $row = ExchangeRate::query()
                ->effectiveAtOrBefore($code, $base, $asOf)
                ->first();
        }

        if ($row !== null) {
            $r = (float) $row->rate;
            self::$resolvedRateCache[$cacheKey] = $r;

            return $r;
        }

        /** @var array<string, float|int|string> $rates */
        $rates = config('currency.rates_to_base', []);
        $upper = strtoupper($code);
        if (array_key_exists($upper, $rates)) {
            $r = (float) $rates[$upper];
            self::$resolvedRateCache[$cacheKey] = $r;

            return $r;
        }

        self::$resolvedRateCache[$cacheKey] = null;

        return null;
    }

    /**
     * Clear per-request cache (e.g. after seeding rates in tests).
     */
    public static function flushResolvedRateCache(): void
    {
        self::$resolvedRateCache = [];
    }

    /**
     * @return array<string, float> code => rate to base (only codes that resolve at {@see $asOf})
     */
    public static function ratesToBase(?Carbon $asOf = null): array
    {
        $asOf ??= Carbon::now();
        $configured = array_keys(config('currency.rates_to_base', []));
        $codes = array_unique(array_merge($configured, SupportedCurrencies::codes()));

        $out = [];
        foreach ($codes as $code) {
            $c = strtoupper((string) $code);
            $r = self::resolveRateToBase($c, $asOf);
            if ($r !== null) {
                $out[$c] = $r;
            }
        }

        return $out;
    }

    public static function hasRate(string $currencyCode, ?Carbon $asOf = null): bool
    {
        return self::resolveRateToBase($currencyCode, $asOf) !== null;
    }

    /**
     * Amount in base currency, or null if no rate exists for the currency at {@see $asOf}.
     */
    public static function toBase(float $amount, string $currencyCode, ?Carbon $asOf = null): ?float
    {
        $rate = self::resolveRateToBase($currencyCode, $asOf);
        if ($rate === null) {
            return null;
        }

        return $amount * $rate;
    }
}
