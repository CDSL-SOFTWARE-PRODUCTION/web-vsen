<?php

namespace App\Domain\Demand;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\Order;
use App\Models\Demand\OrderItem;
use App\Models\Demand\SalesTouchpoint;
use App\Models\Demand\TenderSnapshot;
use App\Models\LegalEntity;
use App\Models\User;
use RuntimeException;

class CreateOrderFromSnapshotCommandService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function handle(TenderSnapshot $snapshot, ?int $actorUserId = null): CreateOrderFromSnapshotResult
    {
        if (! $snapshot->isLocked()) {
            throw new RuntimeException('Cannot create order: tender snapshot is not locked.');
        }
        if ($snapshot->items->count() === 0) {
            throw new RuntimeException('Cannot create order: tender snapshot has no awarded items.');
        }

        $legalEntityId = $actorUserId !== null
            ? User::query()->whereKey($actorUserId)->value('legal_entity_id')
            : null;
        if ($legalEntityId === null) {
            $legalEntityId = LegalEntity::query()->orderBy('id')->value('id');
        }

        $order = new Order([
            'legal_entity_id' => $legalEntityId,
            'order_code' => Order::buildOrderCodeFromTbmt($snapshot->source_notify_no),
            'name' => 'Order from '.$snapshot->source_notify_no,
            'tender_snapshot_id' => $snapshot->id,
            'awarded_at' => now(),
        ]);
        $order->setInitialState('AwardTender');
        $order->save();

        $createdItems = 0;
        foreach ($snapshot->items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'line_no' => $item->line_no,
                'name' => $item->name,
                'uom' => $item->uom,
                'quantity' => (float) $item->quantity_awarded,
                'status' => 'planned',
            ]);
            $createdItems++;
        }

        $result = new CreateOrderFromSnapshotResult(
            orderId: $order->id,
            orderItemsCount: $createdItems
        );

        SalesTouchpoint::query()->create([
            'order_id' => $order->id,
            'activity_type' => 'Other',
            'occurred_at' => now(),
            'summary' => 'Order initialized from locked tender snapshot: '.$snapshot->source_notify_no,
            'created_by_user_id' => $actorUserId,
        ]);

        $this->auditLogService->log(
            actorUserId: $actorUserId,
            entityType: 'TenderSnapshot',
            entityId: $snapshot->id,
            action: 'AwardTenderCommand',
            context: $result->toArray()
        );

        return $result;
    }
}
