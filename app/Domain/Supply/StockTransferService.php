<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function ship(string $itemName, string $sourceWarehouseCode, string $destWarehouseCode, float $quantity, ?int $actorUserId = null): StockTransferResult
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Transfer quantity must be greater than zero.');
        }
        if ($sourceWarehouseCode === $destWarehouseCode) {
            throw new RuntimeException('Source and destination warehouses must be different.');
        }

        $transfer = DB::transaction(function () use ($itemName, $sourceWarehouseCode, $destWarehouseCode, $quantity): StockTransfer {
            $sourceLot = InventoryLot::query()
                ->where('item_name', $itemName)
                ->where('warehouse_code', $sourceWarehouseCode)
                ->first();
            if (! $sourceLot instanceof InventoryLot) {
                throw new RuntimeException("Source warehouse [{$sourceWarehouseCode}] has no inventory lot for item [{$itemName}].");
            }
            if ((float) $sourceLot->available_qty < $quantity) {
                throw new RuntimeException('Source warehouse does not have enough stock for transfer.');
            }

            $sourceBalance = (float) $sourceLot->available_qty - $quantity;
            $sourceLot->update(['available_qty' => $sourceBalance]);

            InventoryLedger::query()->create([
                'inventory_lot_id' => $sourceLot->id,
                'item_name' => $itemName,
                'action' => 'TRANSFER_OUT',
                'qty_change' => -$quantity,
                'balance_after' => $sourceBalance,
            ]);

            return StockTransfer::query()->create([
                'transfer_code' => 'TR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'item_name' => $itemName,
                'source_warehouse_code' => $sourceWarehouseCode,
                'dest_warehouse_code' => $destWarehouseCode,
                'quantity' => $quantity,
                'status' => 'Shipped',
            ]);
        });

        $result = new StockTransferResult(
            transferId: $transfer->id,
            status: $transfer->status
        );
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'StockTransfer',
            entityId: $transfer->id,
            action: 'ShipTransfer',
            context: $result->toArray()
        );

        return $result;
    }

    public function receive(int $transferId, ?int $actorUserId = null): StockTransferResult
    {
        $transfer = StockTransfer::query()->findOrFail($transferId);
        if ($transfer->status !== 'Shipped') {
            throw new RuntimeException('Only shipped transfers can be received.');
        }

        DB::transaction(function () use ($transfer): void {
            $destLot = InventoryLot::query()->firstOrCreate(
                [
                    'item_name' => $transfer->item_name,
                    'warehouse_code' => $transfer->dest_warehouse_code,
                ],
                ['available_qty' => 0]
            );

            $newBalance = (float) $destLot->available_qty + (float) $transfer->quantity;
            $destLot->update(['available_qty' => $newBalance]);

            InventoryLedger::query()->create([
                'inventory_lot_id' => $destLot->id,
                'item_name' => $transfer->item_name,
                'action' => 'TRANSFER_IN',
                'qty_change' => (float) $transfer->quantity,
                'balance_after' => $newBalance,
            ]);

            $transfer->update(['status' => 'Received']);
        });

        $result = new StockTransferResult(
            transferId: $transfer->id,
            status: 'Received'
        );
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'StockTransfer',
            entityId: $transfer->id,
            action: 'ReceiveTransfer',
            context: $result->toArray()
        );

        return $result;
    }
}
