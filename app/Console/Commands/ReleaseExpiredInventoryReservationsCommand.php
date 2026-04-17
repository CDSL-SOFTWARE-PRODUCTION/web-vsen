<?php

namespace App\Console\Commands;

use App\Domain\Supply\ReserveInventoryService;
use Illuminate\Console\Command;

class ReleaseExpiredInventoryReservationsCommand extends Command
{
    protected $signature = 'inventory:release-expired-reservations';

    protected $description = 'Release inventory reservations past expires_at (C-INV-002).';

    public function handle(ReserveInventoryService $reserveInventoryService): int
    {
        $count = $reserveInventoryService->releaseExpired(null);
        $this->info("Released {$count} expired reservation(s).");

        return self::SUCCESS;
    }
}
