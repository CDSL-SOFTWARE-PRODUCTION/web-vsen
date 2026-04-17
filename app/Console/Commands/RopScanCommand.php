<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\InventoryLot;
use Illuminate\Console\Command;

/**
 * C-INV-004: flag lots at or below configured ROP-style threshold (simplified until Product.abc_class exists).
 */
class RopScanCommand extends Command
{
    protected $signature = 'ops:rop-scan';

    protected $description = 'Scan inventory lots below ROP warn threshold and write audit entries.';

    public function handle(AuditLogService $auditLogService): int
    {
        $threshold = (float) config('ops.rop_warn_below_qty', 10);
        $lots = InventoryLot::query()
            ->where('available_qty', '<', $threshold)
            ->orderBy('id')
            ->get();

        foreach ($lots as $lot) {
            $auditLogService->log(
                actorUserId: null,
                entityType: 'InventoryLot',
                entityId: $lot->id,
                action: 'RopScanWarn',
                context: [
                    'constraint' => 'C-INV-004',
                    'available_qty' => (float) $lot->available_qty,
                    'threshold' => $threshold,
                    'item_name' => $lot->item_name,
                    'warehouse_code' => $lot->warehouse_code,
                    'suggest_supply_order_draft' => true,
                ]
            );
        }

        $this->info('ROP scan: '.$lots->count().' lot(s) below threshold '.$threshold.'.');

        return self::SUCCESS;
    }
}
