<?php

namespace App\Domain\Knowledge;

use App\Models\Knowledge\CanonicalProduct;
use RuntimeException;

/**
 * Minimal Knowledge layer: raw name + optional spec JSON → persisted canonical row.
 */
class UpsertCanonicalProductService
{
    public function handle(string $sku, string $rawName, ?array $specJson = null): CanonicalProduct
    {
        $sku = trim($sku);
        if ($sku === '') {
            throw new RuntimeException('SKU is required.');
        }

        return CanonicalProduct::query()->updateOrCreate(
            ['sku' => $sku],
            ['raw_name' => $rawName, 'spec_json' => $specJson]
        );
    }
}
