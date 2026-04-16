<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class ConfirmFulfillmentCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): OrderTransitionResult
    {
        $order = Order::query()->findOrFail($orderId);
        if ($order->state !== 'StartExecution') {
            throw new RuntimeException("Cannot confirm fulfillment from state [{$order->state}].");
        }

        return $this->orderTransitionService->transition($order, 'ConfirmFulfillment', $actorUserId);
    }
}
