<?php

namespace App\Domain\Delivery;

use App\Domain\Audit\AuditLogService;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use App\Support\GeoDistanceMeters;
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

    public function markDelivered(int $deliveryId, ?int $actorUserId = null, ?string $gpsCoordinatesActual = null): Delivery
    {
        return DB::transaction(function () use ($deliveryId, $actorUserId, $gpsCoordinatesActual): Delivery {
            $delivery = Delivery::query()->findOrFail($deliveryId);

            $updates = [
                'status' => 'Delivered',
                'delivered_at' => now(),
            ];
            if ($gpsCoordinatesActual !== null && $gpsCoordinatesActual !== '') {
                $updates['gps_coordinates_actual'] = $gpsCoordinatesActual;
            }
            $delivery->update($updates);
            $delivery = $delivery->fresh();

            $this->assertGpsCompliance($delivery);

            $this->auditLogService->log(
                $actorUserId,
                'Delivery',
                $delivery->id,
                'MarkDelivered',
                [
                    'contract_id' => $delivery->contract_id,
                    'gps_coordinates_actual' => $delivery->gps_coordinates_actual,
                    'expected_gps_coordinates' => $delivery->expected_gps_coordinates,
                ]
            );

            return $delivery;
        });
    }

    /**
     * C-DEL-002: proof GPS vs expected station.
     */
    private function assertGpsCompliance(Delivery $delivery): void
    {
        $mode = (string) config('ops.gates.delivery_gps_compliance', 'warn');
        if ($mode === 'off') {
            return;
        }

        $expected = $delivery->expected_gps_coordinates;
        $actual = $delivery->gps_coordinates_actual;
        if ($expected === null || $expected === '' || $actual === null || $actual === '') {
            return;
        }

        $meters = GeoDistanceMeters::betweenStrings($expected, $actual);
        if ($meters === null) {
            return;
        }

        $max = (float) config('ops.delivery_gps_max_meters', 500);
        if ($meters <= $max) {
            return;
        }

        $msg = 'C-DEL-002: GPS proof is '.round($meters, 1).' m from expected (max '.$max.' m).';
        if ($mode === 'hard') {
            throw new RuntimeException($msg);
        }

        $this->auditLogService->log(
            null,
            'Delivery',
            $delivery->id,
            'DeliveryGpsComplianceWarn',
            [
                'constraint' => 'C-DEL-002',
                'distance_meters' => $meters,
                'max_meters' => $max,
                'message' => $msg,
            ]
        );
    }
}
