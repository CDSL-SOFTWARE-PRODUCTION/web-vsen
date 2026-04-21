<?php

namespace App\Support\Currency;

/**
 * ISO 4217 codes allowed for Ops procurement quotes (expand over time).
 */
final class SupportedCurrencies
{
    /**
     * @return array<string, string> code => localized label
     */
    public static function selectOptions(): array
    {
        return [
            'VND' => 'VND — Đồng Việt Nam',
            'USD' => 'USD — US dollar',
            'CNY' => 'CNY — Chinese yuan',
            'EUR' => 'EUR — Euro',
            'JPY' => 'JPY — Japanese yen',
            'KRW' => 'KRW — South Korean won',
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::selectOptions());
    }
}
