<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\ReturnOrder;
use Illuminate\Support\Facades\DB;

class ProcessReturnOrderService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(int $returnOrderId, ?int $actorUserId = null): ProcessReturnOrderResult
    {
        $returnOrder = ReturnOrder::query()->with('lines')->findOrFail($returnOrderId);
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H7', 'location' => 'ProcessReturnOrderService.php:handle:entry', 'message' => 'Process return order requested', 'data' => ['return_order_id' => $returnOrder->id, 'lines_count' => $returnOrder->lines->count()], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion

        $result = DB::transaction(function () use ($returnOrder): ProcessReturnOrderResult {
            $restocked = 0;
            $disposed = 0;

            foreach ($returnOrder->lines as $line) {
                $qty = (float) $line->quantity;
                if ($line->condition === 'Good') {
                    $lot = InventoryLot::query()->firstOrCreate(
                        [
                            'item_name' => $line->item_name,
                            'warehouse_code' => $line->warehouse_code,
                        ],
                        ['available_qty' => 0]
                    );

                    $newBalance = (float) $lot->available_qty + $qty;
                    $lot->update(['available_qty' => $newBalance]);
                    InventoryLedger::query()->create([
                        'inventory_lot_id' => $lot->id,
                        'item_name' => $line->item_name,
                        'action' => 'RESTOCK',
                        'qty_change' => $qty,
                        'balance_after' => $newBalance,
                    ]);
                    $restocked++;
                } else {
                    $lot = InventoryLot::query()->firstOrCreate(
                        [
                            'item_name' => $line->item_name,
                            'warehouse_code' => $line->warehouse_code,
                        ],
                        ['available_qty' => 0]
                    );
                    InventoryLedger::query()->create([
                        'inventory_lot_id' => $lot->id,
                        'item_name' => $line->item_name,
                        'action' => 'DISPOSE',
                        'qty_change' => -$qty,
                        'balance_after' => (float) $lot->available_qty,
                    ]);
                    $disposed++;
                }
            }

            $returnOrder->update(['status' => 'Processing']);

            return new ProcessReturnOrderResult(
                returnOrderId: $returnOrder->id,
                restockedLinesCount: $restocked,
                disposedLinesCount: $disposed
            );
        });

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'ReturnOrder',
            entityId: $returnOrder->id,
            action: 'ProcessReturnOrder',
            context: $result->toArray()
        );

        return $result;
    }
}
