<?php

namespace App\Filament\Ops\Resources\Finance\ExchangeRateResource\Pages;

use App\Filament\Ops\Resources\Finance\ExchangeRateResource;
use App\Models\Ops\ExchangeRate;
use App\Support\Currency\CurrencyConverter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateExchangeRate extends CreateRecord
{
    protected static string $resource = ExchangeRateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['base_currency'] = CurrencyConverter::baseCurrency();
        $effectiveAt = isset($data['effective_at']) ? Carbon::parse($data['effective_at']) : null;
        if ($effectiveAt === null) {
            throw ValidationException::withMessages([
                'effective_at' => __('validation.required', ['attribute' => 'effective_at']),
            ]);
        }
        $quote = strtoupper((string) ($data['quote_currency'] ?? ''));
        if ($quote === strtoupper(CurrencyConverter::baseCurrency())) {
            throw ValidationException::withMessages([
                'quote_currency' => __('ops.resources.exchange_rate.validation.quote_must_not_be_base'),
            ]);
        }
        if (ExchangeRate::existsDuplicate($quote, (string) $data['base_currency'], $effectiveAt)) {
            throw ValidationException::withMessages([
                'effective_at' => __('ops.resources.exchange_rate.validation.duplicate_effective'),
            ]);
        }

        $data['effective_at'] = $effectiveAt->toDateTimeString();

        return $data;
    }
}
