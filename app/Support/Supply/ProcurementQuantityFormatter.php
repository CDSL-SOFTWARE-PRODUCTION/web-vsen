<?php

namespace App\Support\Supply;

/**
 * Display-only formatting for procurement quantities (required / available / shortage).
 * Stored values may remain decimal; UI shows rounded integers without fractional noise.
 */
final class ProcurementQuantityFormatter
{
    public static function formatDisplay(float|int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (! is_numeric($value)) {
            return '-';
        }

        $n = (int) round((float) $value);
        $vi = app()->getLocale() === 'vi';

        return $vi
            ? number_format($n, 0, ',', '.')
            : number_format($n, 0, '.', ',');
    }

    /**
     * Plain integer string for CSV (no thousand separators). Empty when not numeric.
     */
    public static function formatCsv(float|int|string|null $value): string
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return '';
        }

        return (string) (int) round((float) $value);
    }
}
