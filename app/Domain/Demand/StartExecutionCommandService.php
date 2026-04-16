<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class StartExecutionCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): OrderTransitionResult
    {
        $order = Order::query()->findOrFail($orderId);
        if ($order->state !== 'ConfirmContract') {
            throw new RuntimeException("Cannot start execution from state [{$order->state}].");
        }

        return $this->orderTransitionService->transition($order, 'StartExecution', $actorUserId);
    }
}
