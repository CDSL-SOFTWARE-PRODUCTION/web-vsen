<?php

namespace App\Domain\Demand;

use App\Models\Demand\BidOpeningSession;
use RuntimeException;

final class BidOpeningMappingGateService
{
    public function assertAllLinesMapped(BidOpeningSession $session): void
    {
        $counts = $session->lines()
            ->selectRaw('mapping_status, COUNT(*) as c')
            ->groupBy('mapping_status')
            ->pluck('c', 'mapping_status');

        $unmapped = (int) ($counts['unmapped'] ?? 0);
        $conflict = (int) ($counts['conflict'] ?? 0);
        if ($unmapped === 0 && $conflict === 0) {
            return;
        }

        throw new RuntimeException(
            "Cannot continue procurement flow: {$unmapped} unmapped lines and {$conflict} conflict lines must be resolved."
        );
    }
}
