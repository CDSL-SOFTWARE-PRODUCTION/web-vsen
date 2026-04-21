<?php

namespace App\Filament\Ops\Resources\Finance\ExchangeRateResource\Pages;

use App\Filament\Ops\Resources\Finance\ExchangeRateResource;
use App\Models\Ops\ExchangeRate;
use App\Support\Currency\CurrencyConverter;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EditExchangeRate extends EditRecord
{
    protected static string $resource = ExchangeRateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $effectiveAt = isset($data['effective_at']) ? Carbon::parse($data['effective_at']) : null;
        if ($effectiveAt === null) {
            throw ValidationException::withMessages([
                'effective_at' => __('validation.required', ['attribute' => 'effective_at']),
            ]);
        }
        $quote = strtoupper((string) ($data['quote_currency'] ?? $this->record->quote_currency));
        $base = (string) ($data['base_currency'] ?? $this->record->base_currency);
        if ($quote === strtoupper(CurrencyConverter::baseCurrency())) {
            throw ValidationException::withMessages([
                'quote_currency' => __('ops.resources.exchange_rate.validation.quote_must_not_be_base'),
            ]);
        }
        if (ExchangeRate::existsDuplicate($quote, $base, $effectiveAt, (int) $this->record->id)) {
            throw ValidationException::withMessages([
                'effective_at' => __('ops.resources.exchange_rate.validation.duplicate_effective'),
            ]);
        }
        $data['effective_at'] = $effectiveAt->toDateTimeString();

        return $data;
    }
}
