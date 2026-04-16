<?php

namespace App\Domain\Demand;

final class ConfirmContractResult extends OrderTransitionResult
{
    public static function fromTransitionResult(OrderTransitionResult $result): self
    {
        return new self(
            orderId: $result->orderId,
            command: $result->command,
            fromState: $result->fromState,
            toState: $result->toState,
            warningRaised: $result->warningRaised,
            warnings: $result->warnings
        );
    }
}
