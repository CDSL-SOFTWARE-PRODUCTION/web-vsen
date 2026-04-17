<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use App\Models\Ops\Contract;
use App\Models\Ops\Partner;
use RuntimeException;

/**
 * C-ORD-005: block ConfirmContract when customer has overdue debt over threshold days.
 */
final class CustomerCreditGuard
{
    public const DEFAULT_MAX_OVERDUE_DAYS_BLOCK = 30;

    public function assertConfirmContractAllowed(Order $order): void
    {
        $contract = $order->contracts()->first();
        if (! $contract instanceof Contract || $contract->customer_partner_id === null) {
            return;
        }

        $partner = Partner::query()->find($contract->customer_partner_id);
        if ($partner === null || $partner->type !== 'Customer') {
            return;
        }

        if ((int) $partner->max_overdue_days_cached > self::DEFAULT_MAX_OVERDUE_DAYS_BLOCK) {
            throw new RuntimeException(
                'Cannot confirm contract: customer has overdue debt over '
                .self::DEFAULT_MAX_OVERDUE_DAYS_BLOCK.' days (C-ORD-005).'
            );
        }
    }
}
