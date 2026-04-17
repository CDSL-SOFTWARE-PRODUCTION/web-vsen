<?php

namespace App\Domain\Knowledge;

use App\Models\Knowledge\CanonicalProduct;
use RuntimeException;

/**
 * Sinh SKU ổn định từ bộ facet (spec_json) không cần chuẩn hoá quan hệ:
 * cùng bộ key/value → cùng SKU; trùng DB thì thêm hậu tố -2, -3, ...
 */
class GenerateSkuFromFacetsService
{
    public function generate(array $facets, ?int $ignoreCanonicalProductId = null): string
    {
        $facets = $this->normalizeFacets($facets);
        if ($facets === []) {
            throw new RuntimeException(__('ops.resources.canonical_product.generate_sku_empty_facets'));
        }

        ksort($facets);
        $payload = json_encode($facets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $hash = strtoupper(substr(hash('sha256', (string) $payload), 0, 12));
        $base = 'CP-'.$hash;

        return $this->ensureUniqueSku($base, $ignoreCanonicalProductId);
    }

    /**
     * @param  array<string, mixed>  $facets
     * @return array<string, string>
     */
    private function normalizeFacets(array $facets): array
    {
        $out = [];
        foreach ($facets as $k => $v) {
            $key = is_string($k) ? trim($k) : (string) $k;
            if ($key === '') {
                continue;
            }
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $val = trim((string) $v);
            if ($val === '') {
                continue;
            }
            $out[$key] = $val;
        }

        return $out;
    }

    private function ensureUniqueSku(string $base, ?int $ignoreCanonicalProductId): string
    {
        $sku = $base;
        $n = 2;
        while ($this->skuExists($sku, $ignoreCanonicalProductId)) {
            $suffix = '-'.$n;
            $sku = substr($base, 0, max(1, 64 - strlen($suffix))).$suffix;
            $n++;
        }

        return $sku;
    }

    private function skuExists(string $sku, ?int $ignoreCanonicalProductId): bool
    {
        $q = CanonicalProduct::query()->where('sku', $sku);
        if ($ignoreCanonicalProductId !== null) {
            $q->where('id', '!=', $ignoreCanonicalProductId);
        }

        return $q->exists();
    }
}
