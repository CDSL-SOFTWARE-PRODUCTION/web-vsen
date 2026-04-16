<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\OrderItem;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\InventoryReservation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReserveInventoryService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function reserve(int $orderItemId, int $inventoryLotId, ?float $requestedQty = null, ?int $actorUserId = null): ReserveInventoryResult
    {
        $orderItem = OrderItem::query()->findOrFail($orderItemId);
        $lot = InventoryLot::query()->findOrFail($inventoryLotId);

        $qty = $requestedQty ?? (float) $orderItem->quantity;
        if ($qty <= 0) {
            throw new RuntimeException('Reserved quantity must be greater than zero.');
        }
        if ((float) $lot->available_qty < $qty) {
            throw new RuntimeException('Cannot reserve inventory: requested quantity exceeds available stock.');
        }
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H5', 'location' => 'ReserveInventoryService.php:reserve:validated', 'message' => 'Reserve validated', 'data' => ['order_item_id' => $orderItem->id, 'inventory_lot_id' => $lot->id, 'requested_qty' => $qty, 'available_before' => (float) $lot->available_qty], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion

        $reservation = DB::transaction(function () use ($orderItem, $lot, $qty): InventoryReservation {
            $newBalance = (float) $lot->available_qty - $qty;
            $lot->update(['available_qty' => $newBalance]);

            $reservation = InventoryReservation::query()->create([
                'inventory_lot_id' => $lot->id,
                'order_item_id' => $orderItem->id,
                'reserved_qty' => $qty,
                'status' => 'Reserved',
                'reserved_at' => now(),
            ]);

            InventoryLedger::query()->create([
                'inventory_lot_id' => $lot->id,
                'item_name' => $lot->item_name,
                'action' => 'RESERVE',
                'qty_change' => -$qty,
                'balance_after' => $newBalance,
            ]);

            return $reservation;
        });

        $result = new ReserveInventoryResult(
            orderItemId: $orderItem->id,
            inventoryLotId: $lot->id,
            reservationId: $reservation->id,
            reservedQty: $qty
        );
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'InventoryReservation',
            entityId: $reservation->id,
            action: 'ReserveInventory',
            context: $result->toArray()
        );

        return $result;
    }

    public function release(int $reservationId, ?int $actorUserId = null): void
    {
        $reservation = InventoryReservation::query()->findOrFail($reservationId);
        if ($reservation->status !== 'Reserved') {
            return;
        }
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H5', 'location' => 'ReserveInventoryService.php:release:entry', 'message' => 'Release requested', 'data' => ['reservation_id' => $reservation->id, 'inventory_lot_id' => $reservation->inventory_lot_id, 'reserved_qty' => (float) $reservation->reserved_qty], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion

        DB::transaction(function () use ($reservation): void {
            $lot = InventoryLot::query()->findOrFail($reservation->inventory_lot_id);
            $releasedQty = (float) $reservation->reserved_qty;
            $newBalance = (float) $lot->available_qty + $releasedQty;

            $lot->update(['available_qty' => $newBalance]);
            $reservation->update([
                'status' => 'Released',
                'released_at' => now(),
            ]);

            InventoryLedger::query()->create([
                'inventory_lot_id' => $lot->id,
                'item_name' => $lot->item_name,
                'action' => 'RELEASE',
                'qty_change' => $releasedQty,
                'balance_after' => $newBalance,
            ]);
        });

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'InventoryReservation',
            entityId: $reservation->id,
            action: 'ReleaseInventoryReservation',
            context: ['reservation_id' => $reservation->id]
        );
    }
}
