<?php

namespace App\Support\Currency;

/**
 * Display and decimal-string helpers for procurement amounts.
 *
 * Unit prices: no rounding — preserve digits from DB/forms.
 * Line totals (unit × qty): rounded then formatted with {@see number_format} — 0 decimals for VND/JPY/KRW/IDR, up to 4 for others (avoids binary float noise in display).
 */
final class CurrencyFormatter
{
    /**
     * Format using explicit currency, or {@see CurrencyConverter::legacyDefault} when code is null/empty.
     */
    public static function formatUnitPriceOrLegacy(float|string|null $amount, ?string $currencyCode): string
    {
        $code = is_string($currencyCode) && trim($currencyCode) !== ''
            ? strtoupper(trim($currencyCode))
            : CurrencyConverter::legacyDefault();

        return self::formatUnitPrice($amount, $code);
    }

    /**
     * Format a unit price or line amount for display — preserves fractional digits from string input (no 2-decimal rounding).
     */
    public static function formatUnitPrice(float|string|null $amount, string $currencyCode): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $code = strtoupper(trim($currencyCode));
        if ($code === '') {
            $code = 'VND';
        }

        $normalized = self::normalizeAmountString($amount);
        if ($normalized === null) {
            return '';
        }

        $zeroFraction = self::isZeroFractionCurrency($code);

        return self::formatGroupedAmount($normalized, $zeroFraction).' '.$code;
    }

    /**
     * Normalize to a non-scientific decimal string, or null if empty/invalid.
     */
    public static function normalizeAmountString(float|int|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return null;
            }
            // Avoid binary float noise (e.g. 132.40000000000001) when serializing for display.
            $s = number_format($value, 12, '.', '');
            $s = rtrim(rtrim($s, '0'), '.');

            return $s === '' ? '0' : $s;
        }

        $t = trim((string) $value);
        if ($t === '' || ! is_numeric($t)) {
            return null;
        }

        if (str_contains(strtolower($t), 'e')) {
            return self::normalizeAmountString((float) $t);
        }

        if (str_starts_with($t, '+')) {
            $t = substr($t, 1);
        }

        return $t;
    }

    /**
     * line_total = unit_price × qty using BCMath when available (qty often decimal:3 on supply lines).
     */
    public static function multiplyUnitByQty(string $unitPrice, float|int|string $qty): string
    {
        $u = self::normalizeAmountString($unitPrice);
        $q = self::normalizeAmountString($qty);
        if ($u === null || $q === null) {
            return '0';
        }

        if (! function_exists('bcmul')) {
            return self::normalizeAmountString((float) $u * (float) $q) ?? '0';
        }

        $scale = 12;

        return self::trimMeaninglessFractionZeros(bcmul($u, $q, $scale));
    }

    /**
     * Round a computed line total for storage/display — uses float {@see round()} per currency (not applied to unit prices).
     */
    public static function roundLineTotal(string $decimalAmount, string $currencyCode): string
    {
        $code = strtoupper(trim($currencyCode));
        if ($code === '') {
            $code = 'VND';
        }

        $normalized = self::normalizeAmountString($decimalAmount);
        if ($normalized === null) {
            return '0';
        }

        $precision = self::isZeroFractionCurrency($code) ? 0 : 4;
        $rounded = round((float) $normalized, $precision);

        return self::formatRoundedDecimalString($rounded, $precision);
    }

    /**
     * Stable decimal string after rounding — uses number_format so values like 132.4 never become 132.40000000000001.
     */
    private static function formatRoundedDecimalString(float $value, int $precision): string
    {
        if ($precision === 0) {
            return (string) (int) round($value);
        }

        $s = number_format($value, $precision, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');

        return $s === '' ? '0' : $s;
    }

    private static function isZeroFractionCurrency(string $code): bool
    {
        return in_array($code, ['VND', 'JPY', 'KRW', 'IDR'], true);
    }

    /**
     * Grouping: vi → 1.234,56 — en → 1,234.56
     */
    private static function formatGroupedAmount(string $normalized, bool $zeroFractionCurrency): string
    {
        $neg = str_starts_with($normalized, '-');
        $n = ltrim($normalized, '-+');

        if ($zeroFractionCurrency) {
            $parts = explode('.', $n, 2);
            $whole = $parts[0];

            return ($neg ? '-' : '').self::groupIntegerDigits($whole, app()->getLocale() === 'vi');
        }

        $parts = explode('.', $n, 2);
        $whole = $parts[0];
        $frac = $parts[1] ?? '';
        $frac = rtrim($frac, '0');

        $vi = app()->getLocale() === 'vi';
        $wholeGrouped = self::groupIntegerDigits($whole, $vi);
        if ($frac === '') {
            return ($neg ? '-' : '').$wholeGrouped;
        }

        $sep = $vi ? ',' : '.';

        return ($neg ? '-' : '').$wholeGrouped.$sep.$frac;
    }

    private static function groupIntegerDigits(string $whole, bool $vietnameseStyle): string
    {
        if ($whole === '' || $whole === '0') {
            return '0';
        }

        $neg = str_starts_with($whole, '-');
        $digits = ltrim($whole, '-');
        if ($digits === '') {
            return '0';
        }

        $reversed = strrev($digits);
        $chunks = str_split($reversed, 3);
        $joined = implode($vietnameseStyle ? '.' : ',', $chunks);

        return ($neg ? '-' : '').strrev($joined);
    }

    private static function trimMeaninglessFractionZeros(string $value): string
    {
        if ($value === '') {
            return '0';
        }

        if (! str_contains($value, '.')) {
            return $value;
        }

        return rtrim(rtrim($value, '0'), '.') ?: '0';
    }
}
