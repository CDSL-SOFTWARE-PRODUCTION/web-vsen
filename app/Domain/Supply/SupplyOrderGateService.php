<?php

namespace App\Domain\Supply;

use App\Models\Supply\SupplyOrder;
use RuntimeException;

final class SupplyOrderGateService
{
    /**
     * @return list<string>
     */
    public function evaluate(SupplyOrder $supplyOrder): array
    {
        $warnings = [];

        if ($supplyOrder->supplier_partner_id === null) {
            $warnings[] = 'Supplier partner is required before approval.';
        }

        $lineCount = $supplyOrder->lines->count();
        if ($lineCount === 0) {
            $warnings[] = 'Supply order must contain at least one line.';
        }

        $unmappedLines = $supplyOrder->lines->filter(
            fn ($line): bool => $line->canonical_product_id === null
        )->count();
        if ($unmappedLines > 0) {
            $warnings[] = "{$unmappedLines} supply lines are not mapped to canonical products.";
        }

        $priceDeviationLines = $supplyOrder->lines->filter(
            function ($line): bool {
                if ($line->price_deviation_pct === null) {
                    return false;
                }
                $threshold = (float) config('ops.supply_order_price_deviation_hard_percent', 10);

                return abs((float) $line->price_deviation_pct) > $threshold;
            }
        )->count();
        if ($priceDeviationLines > 0) {
            $warnings[] = "{$priceDeviationLines} supply lines exceed price deviation threshold.";
        }

        return $warnings;
    }

    public function assertPasses(SupplyOrder $supplyOrder): void
    {
        $warnings = $this->evaluate($supplyOrder);
        if ($warnings === []) {
            return;
        }

        throw new RuntimeException('Supply order gate failed: '.implode(' ', $warnings));
    }
}
