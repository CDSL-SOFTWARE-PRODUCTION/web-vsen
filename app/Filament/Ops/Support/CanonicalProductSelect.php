<?php

namespace App\Filament\Ops\Support;

use App\Models\Knowledge\CanonicalProduct;
use Filament\Forms\Components\Select;

class CanonicalProductSelect
{
    public static function make(
        string $name = 'canonical_product_id',
        string $labelKey = 'ops.common.canonical_product',
        string $helperTextKey = 'ops.common.canonical_product_helper',
    ): Select {
        return Select::make($name)
            ->label(__($labelKey))
            ->options(fn (): array => self::searchProducts(''))
            ->searchable()
            ->searchDebounce(400)
            ->native(false)
            ->getSearchResultsUsing(fn (string $search): array => self::searchProducts($search))
            ->getOptionLabelUsing(fn ($value): ?string => self::resolveProductLabel($value))
            ->helperText(__($helperTextKey));
    }

    /**
     * @return array<int, string>
     */
    private static function searchProducts(string $search): array
    {
        $query = CanonicalProduct::query()
            ->orderBy('raw_name')
            ->orderBy('sku')
            ->limit(50);

        $term = trim($search);
        if ($term !== '') {
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('raw_name', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%');
            });
        }

        return $query
            ->get(['id', 'raw_name', 'sku', 'spec_json'])
            ->mapWithKeys(fn (CanonicalProduct $product): array => [$product->id => self::buildProductLabel($product)])
            ->all();
    }

    private static function resolveProductLabel(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $product = CanonicalProduct::query()
            ->find((int) $value, ['id', 'raw_name', 'sku', 'spec_json']);

        if (! $product instanceof CanonicalProduct) {
            return null;
        }

        return self::buildProductLabel($product);
    }

    private static function buildProductLabel(CanonicalProduct $product): string
    {
        $sku = trim((string) $product->sku);
        $rawName = trim((string) $product->raw_name);
        $sizeHint = self::extractSizeHint($product);
        $nameWithSize = $sizeHint !== '' ? "{$rawName} ({$sizeHint})" : $rawName;

        if ($nameWithSize !== '' && $sku !== '') {
            return "{$nameWithSize} — {$sku}";
        }

        return $nameWithSize !== '' ? $nameWithSize : $sku;
    }

    private static function extractSizeHint(CanonicalProduct $product): string
    {
        $spec = $product->spec_json;
        if (! is_array($spec) || $spec === []) {
            return '';
        }

        $matchedValues = [];

        foreach ($spec as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $keyText = mb_strtolower(trim((string) $key), 'UTF-8');
            $valueText = trim((string) $value);

            if ($valueText === '') {
                continue;
            }

            if (
                str_contains($keyText, 'size')
                || str_contains($keyText, 'kich')
                || str_contains($keyText, 'kích')
                || str_contains($keyText, 'quy')
                || str_contains($keyText, 'dimension')
                || str_contains($keyText, 'length')
                || str_contains($keyText, 'width')
                || str_contains($keyText, 'height')
                || str_contains($keyText, 'chieu')
                || str_contains($keyText, 'chiều')
            ) {
                $matchedValues[] = $valueText;
            }
        }

        if ($matchedValues === []) {
            return '';
        }

        return implode(' · ', array_slice(array_values(array_unique($matchedValues)), 0, 2));
    }
}
