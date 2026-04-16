<?php

namespace App\Domain\Supply;

final class ProcessReturnOrderResult
{
    public function __construct(
        public readonly int $returnOrderId,
        public readonly int $restockedLinesCount,
        public readonly int $disposedLinesCount
    ) {
    }

    /**
     * @return array{return_order_id:int,restocked_lines_count:int,disposed_lines_count:int}
     */
    public function toArray(): array
    {
        return [
            'return_order_id' => $this->returnOrderId,
            'restocked_lines_count' => $this->restockedLinesCount,
            'disposed_lines_count' => $this->disposedLinesCount,
        ];
    }
}
