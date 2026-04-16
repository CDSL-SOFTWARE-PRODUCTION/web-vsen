<?php

namespace App\Domain\Execution;

use App\Domain\Audit\AuditLogService;
use App\Domain\Demand\CreateOrderFromSnapshotCommandService;
use App\Domain\Demand\OrderContractProjectionUpdater;
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
        private readonly AuditLogService $auditLogService,
        private readonly OrderContractProjectionUpdater $projectionUpdater,
        private readonly CreateOrderFromSnapshotCommandService $createOrderFromSnapshotCommandService
    ) {
    }

    public function handle(int $snapshotId, ?int $actorUserId = null): Contract
    {
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H1', 'location' => 'GenerateExecutionPlanService.php:handle:entry', 'message' => 'Generate execution plan called', 'data' => ['snapshot_id' => $snapshotId, 'actor_user_id' => $actorUserId], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion
        $snapshot = TenderSnapshot::query()
            ->with(['items', 'attachments'])
            ->findOrFail($snapshotId);

        if (! $snapshot->isLocked()) {
            throw new RuntimeException('Tender snapshot must be locked before generating execution plan.');
        }

        $existingContract = Contract::query()
            ->where('tender_snapshot_id', $snapshot->id)
            ->first();
        if ($existingContract instanceof Contract) {
            // #region agent log
            @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                    'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H2', 'location' => 'GenerateExecutionPlanService.php:handle:idempotent', 'message' => 'Existing contract returned (idempotent)', 'data' => ['snapshot_id' => $snapshot->id, 'contract_id' => $existingContract->id, 'order_id' => $existingContract->order_id], 'timestamp' => round(microtime(true) * 1000)]),
                    'timeout' => 1,
                ],
            ]));
            // #endregion
            $this->auditLogService->log(
                $actorUserId,
                'TenderSnapshot',
                $snapshot->id,
                'GenerateExecutionPlanSkippedExisting',
                [
                    'contract_id' => $existingContract->id,
                    'order_id' => $existingContract->order_id,
                ]
            );

            return $existingContract;
        }

        /** @var Contract $contract */
        $contract = DB::transaction(function () use ($snapshot, $actorUserId): Contract {
            $orderResult = $this->createOrderFromSnapshotCommandService->handle($snapshot, $actorUserId);

            $contract = Contract::query()->create([
                'order_id' => $orderResult->orderId,
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
            $order = $contract->order()->firstOrFail();
            $this->projectionUpdater->syncFromOrder($order, $contract);

            $this->auditLogService->log(
                $actorUserId,
                'TenderSnapshot',
                $snapshot->id,
                'GenerateExecutionPlan',
                [
                    'contract_id' => $contract->id,
                    'order_id' => $orderResult->orderId,
                ]
            );

            return $contract;
        });

        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H1', 'location' => 'GenerateExecutionPlanService.php:handle:exit', 'message' => 'Generate execution plan completed', 'data' => ['snapshot_id' => $snapshot->id, 'contract_id' => $contract->id, 'order_id' => $contract->order_id], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion

        return $contract;
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

