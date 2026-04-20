<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\SupplyOrder;

final class ApproveSupplyOrderService
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

        $this->gateService->assertPasses($supplyOrder);

        $supplyOrder->update([
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by_user_id' => $actorUserId,
            'blocked_reason' => null,
        ]);

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'SupplyOrder',
            entityId: $supplyOrder->id,
            action: 'ApproveSupplyOrder',
            context: []
        );
    }
}
