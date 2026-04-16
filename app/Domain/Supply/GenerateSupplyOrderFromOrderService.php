<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\Order;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\SupplyOrder;
use App\Models\Supply\SupplyOrderLine;
use Illuminate\Support\Facades\DB;

class GenerateSupplyOrderFromOrderService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): GenerateSupplyOrderResult
    {
        $order = Order::query()->with('items')->findOrFail($orderId);

        $shortages = [];
        foreach ($order->items as $item) {
            $availableQty = (float) InventoryLot::query()
                ->where('item_name', $item->name)
                ->sum('available_qty');
            $requiredQty = (float) $item->quantity;
            $shortageQty = max(0.0, $requiredQty - $availableQty);

            if ($shortageQty > 0) {
                $shortages[] = [
                    'order_item_id' => $item->id,
                    'item_name' => $item->name,
                    'required_qty' => $requiredQty,
                    'available_qty' => $availableQty,
                    'shortage_qty' => $shortageQty,
                ];
            }
        }
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H4', 'location' => 'GenerateSupplyOrderFromOrderService.php:handle:shortage-scan', 'message' => 'Shortage scan completed', 'data' => ['order_id' => $order->id, 'items_count' => $order->items->count(), 'shortage_lines_count' => count($shortages)], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion

        if (count($shortages) === 0) {
            $result = new GenerateSupplyOrderResult(
                orderId: $order->id,
                supplyOrderId: null,
                shortageLinesCount: 0
            );
            $this->auditLogService->log(
                actorUserId: $actorUserId,
                entityType: 'Order',
                entityId: $order->id,
                action: 'GenerateSupplyOrderSkippedNoShortage',
                context: $result->toArray()
            );
            return $result;
        }

        $supplyOrder = DB::transaction(function () use ($order, $shortages): SupplyOrder {
            $supplyOrder = SupplyOrder::query()->create([
                'order_id' => $order->id,
                'supply_order_code' => 'SO-' . $order->id . '-' . now()->format('YmdHis'),
                'status' => 'Draft',
            ]);

            foreach ($shortages as $line) {
                SupplyOrderLine::query()->create([
                    'supply_order_id' => $supplyOrder->id,
                    'order_item_id' => $line['order_item_id'],
                    'item_name' => $line['item_name'],
                    'required_qty' => $line['required_qty'],
                    'available_qty' => $line['available_qty'],
                    'shortage_qty' => $line['shortage_qty'],
                ]);
            }

            return $supplyOrder;
        });

        $result = new GenerateSupplyOrderResult(
            orderId: $order->id,
            supplyOrderId: $supplyOrder->id,
            shortageLinesCount: count($shortages)
        );
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'Order',
            entityId: $order->id,
            action: 'GenerateSupplyOrder',
            context: $result->toArray()
        );

        return $result;
    }
}
