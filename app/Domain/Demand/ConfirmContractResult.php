<?php

namespace App\Domain\Demand;

final class ConfirmContractResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly int $orderId,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly bool $warningRaised,
        public readonly array $warnings
    ) {
    }

    /**
     * @return array{
     *   order_id: int,
     *   from_state: string,
     *   to_state: string,
     *   warning_raised: bool,
     *   warnings: list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'warning_raised' => $this->warningRaised,
            'warnings' => $this->warnings,
        ];
    }
}
