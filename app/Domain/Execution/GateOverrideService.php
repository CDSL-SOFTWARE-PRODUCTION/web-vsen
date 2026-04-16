<?php

namespace App\Domain\Execution;

use App\Domain\Audit\AuditLogService;
use App\Models\Ops\Contract;
use InvalidArgumentException;

class GateOverrideService
{
    public function __construct(
        private readonly GateEvaluator $gateEvaluator,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function evaluate(Contract $contract, string $gateType): GateDecision
    {
        $evaluation = $this->evaluateGate($contract, $gateType);

        return new GateDecision(
            gateType: $gateType,
            hasWarnings: $evaluation['hasWarnings'],
            warnings: $evaluation['warnings'],
            overrideApplied: false,
            overrideReason: null,
            auditAction: $this->gateCheckAction($gateType)
        );
    }

    public function override(Contract $contract, string $gateType, string $overrideReason): GateDecision
    {
        $trimmedReason = trim($overrideReason);
        if ($trimmedReason === '') {
            throw new InvalidArgumentException('Override reason is required when applying gate override.');
        }

        $evaluation = $this->evaluateGate($contract, $gateType);

        if (! $evaluation['hasWarnings']) {
            throw new InvalidArgumentException('Gate override is only allowed when warnings are present.');
        }

        return new GateDecision(
            gateType: $gateType,
            hasWarnings: true,
            warnings: $evaluation['warnings'],
            overrideApplied: true,
            overrideReason: $trimmedReason,
            auditAction: $this->gateOverrideAction($gateType)
        );
    }

    public function writeAudit(?int $actorUserId, Contract $contract, GateDecision $decision): void
    {
        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'Contract',
            entityId: $contract->id,
            action: $decision->auditAction,
            context: $decision->toArray()
        );
    }

    private function evaluateGate(Contract $contract, string $gateType): array
    {
        return match ($gateType) {
            'preActivate' => $this->gateEvaluator->evaluatePreActivate($contract),
            'preDelivery' => $this->gateEvaluator->evaluatePreDelivery($contract),
            'prePayment' => $this->gateEvaluator->evaluatePrePayment($contract),
            default => throw new InvalidArgumentException("Unsupported gate type [{$gateType}]."),
        };
    }

    private function gateCheckAction(string $gateType): string
    {
        return match ($gateType) {
            'preActivate' => 'GateCheckPreActivate',
            'preDelivery' => 'GateCheckPreDelivery',
            'prePayment' => 'GateCheckPrePayment',
            default => throw new InvalidArgumentException("Unsupported gate type [{$gateType}]."),
        };
    }

    private function gateOverrideAction(string $gateType): string
    {
        return match ($gateType) {
            'preActivate' => 'GateOverridePreActivate',
            'preDelivery' => 'GateOverridePreDelivery',
            'prePayment' => 'GateOverridePrePayment',
            default => throw new InvalidArgumentException("Unsupported gate type [{$gateType}]."),
        };
    }
}
