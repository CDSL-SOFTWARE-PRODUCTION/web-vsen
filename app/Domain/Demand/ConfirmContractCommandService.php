<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class ConfirmContractCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): ConfirmContractResult
    {
        $order = Order::query()->findOrFail($orderId);
        if (! in_array($order->state, ['AwardTender', 'SubmitTender'], true)) {
            throw new RuntimeException("Cannot confirm contract from state [{$order->state}].");
        }

        $transition = $this->orderTransitionService->transition($order, 'ConfirmContract', $actorUserId);
        return ConfirmContractResult::fromTransitionResult($transition);
    }
}
