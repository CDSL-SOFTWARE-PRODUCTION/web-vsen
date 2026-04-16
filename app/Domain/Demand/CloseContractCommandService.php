<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class CloseContractCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): OrderTransitionResult
    {
        $order = Order::query()->findOrFail($orderId);
        if ($order->state !== 'Fulfilled') {
            throw new RuntimeException("Cannot close contract from state [{$order->state}].");
        }

        return $this->orderTransitionService->transition($order, 'CloseContract', $actorUserId);
    }
}
