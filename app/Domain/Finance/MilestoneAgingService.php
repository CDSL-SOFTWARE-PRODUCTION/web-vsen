<?php

namespace App\Domain\Finance;

use App\Models\Ops\PaymentMilestone;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class MilestoneAgingService
{
    public function refreshAllCachedOverdue(?CarbonInterface $asOf = null): int
    {
        $asOf = $asOf ?? now();
        $asOfDay = $asOf->copy()->startOfDay();

        return DB::transaction(function () use ($asOfDay): int {
            $updated = 0;
            PaymentMilestone::query()->orderBy('id')->chunkById(100, function ($rows) use ($asOfDay, &$updated): void {
                foreach ($rows as $milestone) {
                    $dueDay = $milestone->due_date->copy()->startOfDay();
                    $days = 0;
                    if ($dueDay->lt($asOfDay)) {
                        $days = (int) $dueDay->diffInDays($asOfDay);
                    }
                    if ((int) $milestone->days_overdue_cached !== $days) {
                        $milestone->update(['days_overdue_cached' => $days]);
                        $updated++;
                    }
                }
            });

            return $updated;
        });
    }
}
