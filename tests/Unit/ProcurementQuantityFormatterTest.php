<?php

use App\Support\Supply\ProcurementQuantityFormatter;
use Tests\TestCase;

uses(TestCase::class);

describe('ProcurementQuantityFormatter', function (): void {
    it('formats display as integer with grouping', function (): void {
        app()->setLocale('vi');
        expect(ProcurementQuantityFormatter::formatDisplay('1000.000'))->toBe('1.000');
        expect(ProcurementQuantityFormatter::formatDisplay('1234.6'))->toBe('1.235');

        app()->setLocale('en');
        expect(ProcurementQuantityFormatter::formatDisplay('1000.4'))->toBe('1,000');
    });

    it('returns dash for empty or non-numeric', function (): void {
        expect(ProcurementQuantityFormatter::formatDisplay(null))->toBe('-');
        expect(ProcurementQuantityFormatter::formatDisplay(''))->toBe('-');
        expect(ProcurementQuantityFormatter::formatDisplay('abc'))->toBe('-');
    });

    it('exports csv as plain integer string', function (): void {
        expect(ProcurementQuantityFormatter::formatCsv('99.7'))->toBe('100');
        expect(ProcurementQuantityFormatter::formatCsv(null))->toBe('');
    });
});
