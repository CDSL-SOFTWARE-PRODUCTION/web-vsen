<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\OrderItem;
use App\Models\Ops\Partner;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\InventoryReservation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReserveInventoryService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function reserve(int $orderItemId, int $inventoryLotId, ?float $requestedQty = null, ?int $actorUserId = null): ReserveInventoryResult
    {
        $orderItem = OrderItem::query()->with(['order.contracts'])->findOrFail($orderItemId);
        $lot = InventoryLot::query()->findOrFail($inventoryLotId);

        $qty = $requestedQty ?? (float) $orderItem->quantity;
        if ($qty <= 0) {
            throw new RuntimeException('Reserved quantity must be greater than zero.');
        }
        if ((float) $lot->available_qty < $qty) {
            throw new RuntimeException('Cannot reserve inventory: requested quantity exceeds available stock.');
        }

        $ttlDays = max(1, (int) config('ops.reserve_ttl_days', 30));
        $order = $orderItem->order;
        $contract = $order?->contracts->first();
        if ($contract !== null && $contract->customer_partner_id !== null) {
            $partner = Partner::query()->find($contract->customer_partner_id);
            if ($partner !== null && $partner->reserve_ttl_days !== null) {
                $ttlDays = max(1, (int) $partner->reserve_ttl_days);
            }
        }
        $expiresAt = now()->addDays($ttlDays);

        $reservation = DB::transaction(function () use ($orderItem, $lot, $qty, $expiresAt): InventoryReservation {
            $newBalance = (float) $lot->available_qty - $qty;
            $lot->update(['available_qty' => $newBalance]);

            $reservation = InventoryReservation::query()->create([
                'inventory_lot_id' => $lot->id,
                'order_item_id' => $orderItem->id,
                'reserved_qty' => $qty,
                'status' => 'Reserved',
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
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
        $priorityKey = $order !== null ? (string) ($order->fulfillment_priority ?? 'contract') : 'contract';
        $priorityScore = (int) config('ops.reserve_priority.'.$priorityKey, config('ops.reserve_priority.contract', 20));

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'InventoryReservation',
            entityId: $reservation->id,
            action: 'ReserveInventory',
            context: array_merge($result->toArray(), [
                'reserve_ttl_days' => $ttlDays,
                'fulfillment_priority' => $priorityKey,
                'reserve_priority_score' => $priorityScore,
            ])
        );

        return $result;
    }

    public function release(int $reservationId, ?int $actorUserId = null): void
    {
        $reservation = InventoryReservation::query()->findOrFail($reservationId);
        if ($reservation->status !== 'Reserved') {
            return;
        }

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

    /**
     * C-INV-002: release reservations past expires_at (cron).
     */
    public function releaseExpired(?int $actorUserId = null): int
    {
        $ids = InventoryReservation::query()
            ->where('status', 'Reserved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        foreach ($ids as $id) {
            $this->release((int) $id, $actorUserId);
        }

        return $ids->count();
    }
}
