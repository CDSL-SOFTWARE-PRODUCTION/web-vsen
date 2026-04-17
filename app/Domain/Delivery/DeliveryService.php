<?php

namespace App\Domain\Delivery;

use App\Domain\Audit\AuditLogService;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function registerDispatch(Contract $contract, array $attributes, ?int $actorUserId = null): Delivery
    {
        if ($contract->order_id === null) {
            throw new RuntimeException('Contract has no order_id for delivery.');
        }

        return DB::transaction(function () use ($contract, $attributes, $actorUserId): Delivery {
            $delivery = Delivery::query()->create([
                'order_id' => $contract->order_id,
                'contract_id' => $contract->id,
                'source_warehouse_code' => $attributes['source_warehouse_code'] ?? null,
                'vehicle_id' => $attributes['vehicle_id'] ?? null,
                'route_type' => $attributes['route_type'] ?? null,
                'tracking_code' => $attributes['tracking_code'] ?? null,
                'status' => $attributes['status'] ?? 'Dispatched',
                'dispatched_at' => $attributes['dispatched_at'] ?? now(),
            ]);

            $this->auditLogService->log(
                $actorUserId,
                'Delivery',
                $delivery->id,
                'RegisterDispatch',
                ['contract_id' => $contract->id, 'order_id' => $contract->order_id]
            );

            return $delivery;
        });
    }

    public function markDelivered(int $deliveryId, ?int $actorUserId = null): Delivery
    {
        return DB::transaction(function () use ($deliveryId, $actorUserId): Delivery {
            $delivery = Delivery::query()->findOrFail($deliveryId);
            $delivery->update([
                'status' => 'Delivered',
                'delivered_at' => now(),
            ]);

            $this->auditLogService->log(
                $actorUserId,
                'Delivery',
                $delivery->id,
                'MarkDelivered',
                ['contract_id' => $delivery->contract_id]
            );

            return $delivery->fresh();
        });
    }
}
