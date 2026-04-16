<?php

namespace App\Domain\Execution;

final class GateDecision
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly string $gateType,
        public readonly bool $hasWarnings,
        public readonly array $warnings,
        public readonly bool $overrideApplied,
        public readonly ?string $overrideReason,
        public readonly string $auditAction
    ) {
    }

    /**
     * @return array{
     *   gateType: string,
     *   hasWarnings: bool,
     *   warnings: list<string>,
     *   overrideApplied: bool,
     *   overrideReason: string|null,
     *   auditAction: string
     * }
     */
    public function toArray(): array
    {
        return [
            'gateType' => $this->gateType,
            'hasWarnings' => $this->hasWarnings,
            'warnings' => $this->warnings,
            'overrideApplied' => $this->overrideApplied,
            'overrideReason' => $this->overrideReason,
            'auditAction' => $this->auditAction,
        ];
    }
}
