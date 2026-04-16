<?php

namespace App\Domain\Demand;

final class CreateOrderFromSnapshotResult
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $orderItemsCount
    ) {
    }

    /**
     * @return array{order_id:int,order_items_count:int}
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_items_count' => $this->orderItemsCount,
        ];
    }
}
