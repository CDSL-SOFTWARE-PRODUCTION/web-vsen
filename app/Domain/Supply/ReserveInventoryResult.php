<?php

namespace App\Domain\Supply;

final class ReserveInventoryResult
{
    public function __construct(
        public readonly int $orderItemId,
        public readonly int $inventoryLotId,
        public readonly int $reservationId,
        public readonly float $reservedQty
    ) {
    }

    /**
     * @return array{order_item_id:int,inventory_lot_id:int,reservation_id:int,reserved_qty:float}
     */
    public function toArray(): array
    {
        return [
            'order_item_id' => $this->orderItemId,
            'inventory_lot_id' => $this->inventoryLotId,
            'reservation_id' => $this->reservationId,
            'reserved_qty' => $this->reservedQty,
        ];
    }
}
