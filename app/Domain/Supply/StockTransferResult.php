<?php

namespace App\Domain\Supply;

final class StockTransferResult
{
    public function __construct(
        public readonly int $transferId,
        public readonly string $status
    ) {
    }

    /**
     * @return array{transfer_id:int,status:string}
     */
    public function toArray(): array
    {
        return [
            'transfer_id' => $this->transferId,
            'status' => $this->status,
        ];
    }
}
