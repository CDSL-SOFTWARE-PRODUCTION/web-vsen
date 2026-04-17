<?php

namespace App\Domain\Execution;

use App\Models\Ops\Contract;

final class FulfillmentReadiness
{
    public static function hasDeliveredShipment(Contract $contract): bool
    {
        return $contract->deliveries()->where('status', 'Delivered')->exists();
    }

    public static function hasAcceptanceMinuteNotMissing(Contract $contract): bool
    {
        return $contract->documents()
            ->where('document_type', 'Acceptance Minute')
            ->where('status', '!=', 'missing')
            ->exists();
    }
}
