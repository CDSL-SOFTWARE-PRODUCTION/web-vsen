<?php

namespace App\Domain\Execution;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\Order;
use App\Models\Demand\OrderItem;
use App\Models\Demand\TenderSnapshot;
use App\Models\Ops\Contract;
use App\Models\Ops\ContractItem;
use App\Models\Ops\Document;
use App\Models\Ops\PaymentMilestone;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateExecutionPlanService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function handle(int $snapshotId, ?int $actorUserId = null): Contract
    {
        $snapshot = TenderSnapshot::query()
            ->with(['items', 'attachments'])
            ->findOrFail($snapshotId);

        if (! $snapshot->isLocked()) {
            throw new RuntimeException('Tender snapshot must be locked before generating execution plan.');
        }

        /** @var Contract $contract */
        $contract = DB::transaction(function () use ($snapshot, $actorUserId): Contract {
            $order = $this->createOrderFromSnapshot($snapshot);

            $contract = Contract::query()->create([
                'order_id' => $order->id,
                'tender_snapshot_ref' => $snapshot->source_notify_no . ':' . ($snapshot->snapshot_hash ?? 'na'),
                'tender_snapshot_id' => $snapshot->id,
                'contract_code' => 'CT-' . $snapshot->id . '-' . now()->format('YmdHis'),
                'name' => 'Execution plan for ' . $snapshot->source_notify_no,
                'customer_name' => 'Public Procurement',
                'allocated_budget' => 0,
                'risk_level' => 'Green',
            ]);

            foreach ($snapshot->items as $item) {
                ContractItem::query()->create([
                    'contract_id' => $contract->id,
                    'item_code' => $item->tender_item_ref ?: ('LINE-' . $item->line_no),
                    'name' => $item->name,
                    'spec' => $item->spec_committed_raw,
                    'quantity' => max(1, (int) round((float) $item->quantity_awarded)),
                    'delivery_deadline' => now()->addDays(30),
                    'lead_time_days' => 7,
                    'status' => 'not_ordered',
                    'docs_status' => 'missing',
                    'cash_status' => 'not_needed',
                    'is_critical' => false,
                    'line_risk_level' => 'Green',
                ]);
            }

            $this->seedChecklistDocuments($contract->id);
            $this->seedDefaultMilestone($contract->id);

            $this->auditLogService->log(
                $actorUserId,
                'TenderSnapshot',
                $snapshot->id,
                'GenerateExecutionPlan',
                [
                    'contract_id' => $contract->id,
                    'order_id' => $order->id,
                ]
            );

            return $contract;
        });

        return $contract;
    }

    private function createOrderFromSnapshot(TenderSnapshot $snapshot): Order
    {
        $order = Order::query()->create([
            'order_code' => 'ORD-' . $snapshot->id . '-' . now()->format('YmdHis'),
            'name' => 'Order from ' . $snapshot->source_notify_no,
            'state' => 'AwardTender',
            'tender_snapshot_id' => $snapshot->id,
            'awarded_at' => now(),
        ]);

        foreach ($snapshot->items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'line_no' => $item->line_no,
                'name' => $item->name,
                'uom' => $item->uom,
                'quantity' => (float) $item->quantity_awarded,
                'status' => 'planned',
            ]);
        }

        return $order;
    }

    private function seedChecklistDocuments(int $contractId): void
    {
        $documentTemplates = [
            ['group' => 'source', 'type' => 'Tender Snapshot'],
            ['group' => 'quality_legal', 'type' => 'CO-CQ'],
            ['group' => 'delivery_install', 'type' => 'Delivery Record'],
            ['group' => 'acceptance_payment', 'type' => 'Acceptance Minute'],
        ];

        foreach ($documentTemplates as $template) {
            Document::query()->create([
                'contract_id' => $contractId,
                'document_group' => $template['group'],
                'document_type' => $template['type'],
                'status' => 'missing',
            ]);
        }
    }

    private function seedDefaultMilestone(int $contractId): void
    {
        PaymentMilestone::query()->create([
            'contract_id' => $contractId,
            'name' => 'Milestone 1',
            'due_date' => now()->addDays(45)->toDateString(),
            'amount_planned' => 0,
            'checklist_status' => 'pending',
            'payment_ready' => false,
        ]);
    }
}

