<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\SupplyOrder;

final class RequestSupplyOrderApprovalService
{
    public function __construct(
        private readonly SupplyOrderGateService $gateService,
        private readonly AuditLogService $auditLogService
    ) {}

    public function handle(int $supplyOrderId, ?int $actorUserId = null): void
    {
        $supplyOrder = SupplyOrder::query()
            ->with('lines')
            ->findOrFail($supplyOrderId);

        $warnings = $this->gateService->evaluate($supplyOrder);

        $supplyOrder->update([
            'status' => 'PendingApproval',
            'approval_requested_at' => now(),
            'blocked_reason' => $warnings === [] ? null : implode(' ', $warnings),
        ]);

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'SupplyOrder',
            entityId: $supplyOrder->id,
            action: 'RequestApproval',
            context: [
                'warnings' => $warnings,
            ]
        );
    }
}
