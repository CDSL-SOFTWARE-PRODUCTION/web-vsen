<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use RuntimeException;

class ConfirmContractCommandService
{
    public function __construct(
        private readonly OrderTransitionService $orderTransitionService,
        private readonly CustomerCreditGuard $customerCreditGuard,
        private readonly OrderConstraintChecks $orderConstraintChecks
    ) {}

    public function handle(int $orderId, ?int $actorUserId = null): ConfirmContractResult
    {
        $order = Order::query()->with(['items.priceListItem', 'contracts'])->findOrFail($orderId);
        if (! in_array($order->state, ['AwardTender', 'SubmitTender'], true)) {
            throw new RuntimeException("Cannot confirm contract from state [{$order->state}].");
        }

        $this->customerCreditGuard->assertConfirmContractAllowed($order);

        $contract = $order->contracts()->first();
        if ($contract !== null) {
            $this->orderConstraintChecks->assertConfirmContractCreditLimit($order, $contract);
        }

        $transition = $this->orderTransitionService->transition($order, 'ConfirmContract', $actorUserId);

        return ConfirmContractResult::fromTransitionResult($transition);
    }
}
