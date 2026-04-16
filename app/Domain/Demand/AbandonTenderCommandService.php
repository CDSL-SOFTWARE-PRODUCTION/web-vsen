<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class AbandonTenderCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): OrderTransitionResult
    {
        $order = Order::query()->findOrFail($orderId);
        if ($order->state !== 'SubmitTender') {
            throw new RuntimeException("Cannot abandon tender from state [{$order->state}].");
        }

        return $this->orderTransitionService->transition($order, 'AbandonTender', $actorUserId);
    }
}
