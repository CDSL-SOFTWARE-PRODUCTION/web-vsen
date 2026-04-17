<?php

namespace App\Http\Controllers\Api;

use App\Domain\Delivery\DeliveryService;
use App\Models\Ops\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Phase 6: driver marks delivery with GPS proof (C-DEL-002 validated in DeliveryService).
 */
final class DriverDeliveryController
{
    public function markDelivered(Request $request, int $delivery): JsonResponse
    {
        $validated = $request->validate([
            'gps_coordinates_actual' => ['required', 'string', 'max:120'],
        ]);

        try {
            $d = app(DeliveryService::class)->markDelivered(
                $delivery,
                null,
                $validated['gps_coordinates_actual']
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'id' => $d->id,
            'status' => $d->status,
            'gps_coordinates_actual' => $d->gps_coordinates_actual,
        ]);
    }

    public function show(int $delivery): JsonResponse
    {
        $d = Delivery::query()->findOrFail($delivery);

        return response()->json([
            'id' => $d->id,
            'contract_id' => $d->contract_id,
            'order_id' => $d->order_id,
            'status' => $d->status,
            'expected_gps_coordinates' => $d->expected_gps_coordinates,
        ]);
    }
}
