<?php

namespace App\Domain\Supply;

final class ReceiveSupplyOrderResult
{
    public function __construct(
        public readonly int $supplyOrderId,
        public readonly int $receivedLinesCount
    ) {
    }

    /**
     * @return array{supply_order_id:int,received_lines_count:int}
     */
    public function toArray(): array
    {
        return [
            'supply_order_id' => $this->supplyOrderId,
            'received_lines_count' => $this->receivedLinesCount,
        ];
    }
}
