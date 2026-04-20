<?php

namespace App\Domain\Supply;

use App\Domain\Audit\AuditLogService;
use App\Models\Supply\SupplyOrder;
use RuntimeException;

final class MarkSupplyOrderOrderedService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function handle(int $supplyOrderId, ?int $actorUserId = null): void
    {
        $supplyOrder = SupplyOrder::query()->findOrFail($supplyOrderId);
        if ($supplyOrder->status !== 'Approved') {
            throw new RuntimeException('Supply order can only be moved to Ordered from Approved.');
        }

        $supplyOrder->update([
            'status' => 'Ordered',
            'blocked_reason' => null,
        ]);

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'SupplyOrder',
            entityId: $supplyOrder->id,
            action: 'MarkSupplyOrderOrdered',
            context: []
        );
    }
}
