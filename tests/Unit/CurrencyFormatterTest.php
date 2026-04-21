<?php

use App\Support\Currency\CurrencyConverter;
use App\Support\Currency\CurrencyFormatter;
use App\Support\Currency\SupportedCurrencies;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    CurrencyConverter::flushResolvedRateCache();
});

describe('SupportedCurrencies', function (): void {
    it('lists ISO codes', function (): void {
        expect(SupportedCurrencies::codes())->toContain('VND', 'USD', 'CNY');
    });
});

describe('CurrencyFormatter', function (): void {
    it('formats amounts without throwing', function (): void {
        $out = CurrencyFormatter::formatUnitPrice(1234.5, 'VND');
        expect($out)->toBeString()->not->toBe('');
    });

    it('uses legacy default when currency missing', function (): void {
        $out = CurrencyFormatter::formatUnitPriceOrLegacy(100, null);
        expect($out)->toBeString()->not->toBe('');
    });

    it('rounds line totals to four decimal places for USD without float noise', function (): void {
        expect(CurrencyFormatter::roundLineTotal('10.004', 'USD'))->toBe('10.004');
        expect(CurrencyFormatter::roundLineTotal('10.00501', 'USD'))->toBe('10.005');
        expect(CurrencyFormatter::roundLineTotal('132.4', 'USD'))->toBe('132.4');
    });

    it('rounds line totals to integer for VND', function (): void {
        expect(CurrencyFormatter::roundLineTotal('12345.7', 'VND'))->toBe('12346');
    });
});

describe('CurrencyConverter', function (): void {
    it('converts to base using configured rates', function (): void {
        $usdRate = (float) config('currency.rates_to_base.USD');
        expect(CurrencyConverter::toBase(2, 'USD'))->toBe(2.0 * $usdRate);
        expect(CurrencyConverter::toBase(1000, 'VND'))->toBe(1000.0);
    });

    it('returns null for unknown currency codes', function (): void {
        expect(CurrencyConverter::toBase(1, 'GBP'))->toBeNull();
    });
});
