<?php

/**
 * Base currency is VND: rates_to_base = how many VND for 1 unit of the given ISO 4217 code.
 * Ops: the latest exchange_rates row (effective_at <= conversion time) overrides these values.
 * Fallback: .env / defaults below when no DB row exists.
 */
return [
    'base' => env('CURRENCY_BASE', 'VND'),

    /** When a price row has no currency column (legacy), assume this code for display and conversion. */
    'legacy_default' => env('CURRENCY_LEGACY_DEFAULT', 'VND'),

    /**
     * Multiply amount in `currency` by this rate to get amount in `base` (VND).
     * Only codes listed here participate in cross-currency comparison on the supply matrix.
     */
    'rates_to_base' => [
        'VND' => 1,
        'USD' => (float) env('CURRENCY_RATE_USD_TO_VND', 26_650),
        'CNY' => (float) env('CURRENCY_RATE_CNY_TO_VND', 3_500),
        'EUR' => (float) env('CURRENCY_RATE_EUR_TO_VND', 27_000),
        'JPY' => (float) env('CURRENCY_RATE_JPY_TO_VND', 170),
        'KRW' => (float) env('CURRENCY_RATE_KRW_TO_VND', 18),
    ],
];
