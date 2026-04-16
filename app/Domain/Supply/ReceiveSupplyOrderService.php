<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\SupplyOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReceiveSupplyOrderService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(int $supplyOrderId, ?int $actorUserId = null): ReceiveSupplyOrderResult
    {
        $supplyOrder = SupplyOrder::query()->with('lines')->findOrFail($supplyOrderId);
        if ($supplyOrder->lines->count() === 0) {
            throw new RuntimeException('Cannot receive supply order without lines.');
        }

        $receivedLinesCount = DB::transaction(function () use ($supplyOrder): int {
            $count = 0;

            foreach ($supplyOrder->lines as $line) {
                $qtyToReceive = (float) $line->shortage_qty;
                if ($qtyToReceive <= 0) {
                    continue;
                }

                $lot = InventoryLot::query()->firstOrCreate(
                    ['item_name' => $line->item_name],
                    ['available_qty' => 0]
                );

                $newBalance = (float) $lot->available_qty + $qtyToReceive;
                $lot->update(['available_qty' => $newBalance]);

                InventoryLedger::query()->create([
                    'inventory_lot_id' => $lot->id,
                    'supply_order_id' => $supplyOrder->id,
                    'supply_order_line_id' => $line->id,
                    'item_name' => $line->item_name,
                    'action' => 'IN',
                    'qty_change' => $qtyToReceive,
                    'balance_after' => $newBalance,
                ]);

                $line->update([
                    'received_qty' => (float) $line->received_qty + $qtyToReceive,
                    'shortage_qty' => 0,
                    'status' => 'Received',
                ]);
                $count++;
            }

            $supplyOrder->update(['status' => 'Received']);
            return $count;
        });

        $result = new ReceiveSupplyOrderResult(
            supplyOrderId: $supplyOrder->id,
            receivedLinesCount: $receivedLinesCount
        );
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'SupplyOrder',
            entityId: $supplyOrder->id,
            action: 'ReceiveSupplyOrder',
            context: $result->toArray()
        );

        return $result;
    }
}
