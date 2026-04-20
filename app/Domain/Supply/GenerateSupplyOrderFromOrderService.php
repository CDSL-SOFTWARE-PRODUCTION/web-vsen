<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Domain\Demand\BidOpeningMappingGateService;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\Demand\Order;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\SupplyOrder;
use App\Models\Supply\SupplyOrderLine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateSupplyOrderFromOrderService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BidOpeningMappingGateService $mappingGateService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): GenerateSupplyOrderResult
    {
        $order = Order::query()->with(['items', 'snapshot.bidOpeningSessions'])->findOrFail($orderId);

        $this->assertOrderItemsMapped($order);
        $this->assertSnapshotMapped($order);

        $shortages = [];
        foreach ($order->items as $item) {
            $availableQty = (float) InventoryLot::query()
                ->where('item_name', $item->name)
                ->sum('available_qty');
            $requiredQty = (float) $item->quantity;
            $shortageQty = max(0.0, $requiredQty - $availableQty);

            if ($shortageQty > 0) {
                $referenceUnitPrice = $item->unit_price !== null ? (float) $item->unit_price : null;
                $plannedUnitPrice = $referenceUnitPrice;
                $deviationPct = null;
                $deviationFlag = false;
                if ($plannedUnitPrice !== null && $referenceUnitPrice !== null && $referenceUnitPrice > 0) {
                    $deviationPct = (($plannedUnitPrice - $referenceUnitPrice) / $referenceUnitPrice) * 100;
                    $warnThreshold = (float) config('ops.supply_order_price_deviation_hard_percent', 10);
                    $deviationFlag = abs($deviationPct) > $warnThreshold;
                }
                $shortages[] = [
                    'order_item_id' => $item->id,
                    'canonical_product_id' => $item->canonical_product_id,
                    'item_name' => $item->name,
                    'required_qty' => $requiredQty,
                    'available_qty' => $availableQty,
                    'shortage_qty' => $shortageQty,
                    'planned_unit_price' => $plannedUnitPrice,
                    'reference_unit_price' => $referenceUnitPrice,
                    'price_deviation_pct' => $deviationPct,
                    'price_deviation_flag' => $deviationFlag,
                ];
            }
        }

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
                    'canonical_product_id' => $line['canonical_product_id'],
                    'item_name' => $line['item_name'],
                    'required_qty' => $line['required_qty'],
                    'available_qty' => $line['available_qty'],
                    'shortage_qty' => $line['shortage_qty'],
                    'planned_unit_price' => $line['planned_unit_price'],
                    'reference_unit_price' => $line['reference_unit_price'],
                    'price_deviation_pct' => $line['price_deviation_pct'],
                    'price_deviation_flag' => $line['price_deviation_flag'],
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

    private function assertOrderItemsMapped(Order $order): void
    {
        if (CanonicalProduct::query()->count() === 0) {
            return;
        }

        $unmapped = $order->items
            ->filter(fn ($item): bool => $item->canonical_product_id === null)
            ->values();
        $unmappedCount = $unmapped->count();
        if ($unmappedCount > 0) {
            $examples = $unmapped
                ->take(3)
                ->map(fn ($item): string => trim((string) $item->name) !== '' ? (string) $item->name : '#'.$item->id)
                ->implode(', ');
            $suffix = $examples !== '' ? " Example items: {$examples}." : '';

            throw new RuntimeException("Cannot generate supply order: {$unmappedCount} order items are not mapped to canonical products. Please map canonical products for all order items first.{$suffix}");
        }
    }

    private function assertSnapshotMapped(Order $order): void
    {
        $session = $order->snapshot?->bidOpeningSessions()
            ->with('lines')
            ->latest('opened_at')
            ->latest('id')
            ->first();
        if ($session === null) {
            return;
        }

        $this->mappingGateService->assertAllLinesMapped($session);
    }
}
