<?php

namespace App\Domain\Demand;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\Order;
use App\Models\Ops\Contract;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmContractCommandService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(int $orderId, ?int $actorUserId = null): ConfirmContractResult
    {
        // FETCH
        $order = Order::query()->with(['contracts.documents', 'contracts.issues'])->findOrFail($orderId);
        $fromState = $order->state;

        // VALIDATE
        $contract = $order->contracts->first();
        if (! $contract instanceof Contract) {
            throw new RuntimeException('Cannot confirm contract: missing runtime contract projection.');
        }
        if ($fromState !== 'AwardTender') {
            throw new RuntimeException("Cannot confirm contract from state [{$fromState}].");
        }
        if ($contract->tender_snapshot_id === null) {
            throw new RuntimeException('Cannot confirm contract: contract is not linked to tender snapshot proof.');
        }

        // TRANSFORM
        $warnings = $this->computeWarnings($contract);
        $result = new ConfirmContractResult(
            orderId: $order->id,
            fromState: $fromState,
            toState: 'ConfirmContract',
            warningRaised: count($warnings) > 0,
            warnings: $warnings
        );

        // PERSIST
        DB::transaction(function () use ($order, $contract, $actorUserId, $result): void {
            $order->transitionTo('ConfirmContract');

            $contract->update([
                'risk_level' => $result->warningRaised ? 'Amber' : $contract->risk_level,
                'open_issues_count' => $contract->issues()->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])->count(),
                'missing_docs_count' => $contract->documents()->where('status', 'missing')->count(),
            ]);

            $this->auditLogService->log(
                actorUserId: $actorUserId,
                entityType: 'Order',
                entityId: $order->id,
                action: 'ConfirmContractCommand',
                context: $result->toArray()
            );
        });

        // PRESENT
        return $result;
    }

    /**
     * @return list<string>
     */
    private function computeWarnings(Contract $contract): array
    {
        $warnings = [];
        $missingDocs = $contract->documents()->where('status', 'missing')->count();
        if ($missingDocs > 0) {
            $warnings[] = "Missing documents at confirmation: {$missingDocs}.";
        }

        $openIssues = $contract->issues()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->count();
        if ($openIssues > 0) {
            $warnings[] = "Open execution issues at confirmation: {$openIssues}.";
        }

        return $warnings;
    }
}
