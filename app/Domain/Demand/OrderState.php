<?php

namespace App\Domain\Demand;

/**
 * Bridges persisted {@see Order::$state} strings (runtime/command-oriented) to
 * canonical names in model/states.yaml (BidSubmitted, ContractSigned, …).
 */
final class OrderState
{
    public const RUNTIME_SUBMIT_TENDER = 'SubmitTender';

    public const RUNTIME_AWARD_TENDER = 'AwardTender';

    public const RUNTIME_CONFIRM_CONTRACT = 'ConfirmContract';

    public const RUNTIME_START_EXECUTION = 'StartExecution';

    public const RUNTIME_FULFILLED = 'Fulfilled';

    public const RUNTIME_CONTRACT_CLOSED = 'ContractClosed';

    public const RUNTIME_ABANDONED = 'Abandoned';

    /** @var array<string, string> runtime => canonical */
    private const RUNTIME_TO_CANONICAL = [
        self::RUNTIME_SUBMIT_TENDER => 'BidSubmitted',
        self::RUNTIME_AWARD_TENDER => 'WonWaiting',
        self::RUNTIME_CONFIRM_CONTRACT => 'ContractSigned',
        self::RUNTIME_START_EXECUTION => 'InExecution',
        self::RUNTIME_FULFILLED => 'Fulfilled',
        self::RUNTIME_CONTRACT_CLOSED => 'ContractClosed',
        self::RUNTIME_ABANDONED => 'Abandoned',
    ];

    /**
     * @return list<string>
     */
    public static function allRuntimeStates(): array
    {
        return array_keys(self::RUNTIME_TO_CANONICAL);
    }

    public static function runtimeToCanonical(string $runtime): string
    {
        return self::RUNTIME_TO_CANONICAL[$runtime] ?? $runtime;
    }

    public static function canonicalToRuntime(string $canonical): ?string
    {
        $flip = array_flip(self::RUNTIME_TO_CANONICAL);

        return $flip[$canonical] ?? null;
    }
}
