<?php

namespace App\Domain\Supply;

final class GenerateSupplyOrderResult
{
    public function __construct(
        public readonly int $orderId,
        public readonly ?int $supplyOrderId,
        public readonly int $shortageLinesCount
    ) {
    }

    /**
     * @return array{order_id:int,supply_order_id:int|null,shortage_lines_count:int}
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'supply_order_id' => $this->supplyOrderId,
            'shortage_lines_count' => $this->shortageLinesCount,
        ];
    }
}
