<?php

use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\GateEvaluator;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\Ops\Contract;
use App\Models\Ops\PaymentMilestone;
use App\Models\System\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates execution runtime from locked snapshot with order linkage', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-GEN-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item A',
        'uom' => 'Cai',
        'quantity_awarded' => 12,
        'spec_committed_raw' => 'Spec A',
    ]);

    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    expect($contract->tender_snapshot_id)->toBe($snapshot->id)
        ->and($contract->order_id)->not->toBeNull()
        ->and($contract->items()->count())->toBe(1)
        ->and($contract->documents()->count())->toBeGreaterThan(0)
        ->and($contract->paymentMilestones()->count())->toBeGreaterThan(0);

    $order = Order::query()->findOrFail($contract->order_id);
    expect($order->tender_snapshot_id)->toBe($snapshot->id);
});

it('emits warnings for pre delivery and pre payment gates', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-GATE-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item B',
        'uom' => 'Bo',
        'quantity_awarded' => 3,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $evaluator = app(GateEvaluator::class);

    $preDelivery = $evaluator->evaluatePreDelivery($contract);
    $prePayment = $evaluator->evaluatePrePayment($contract);

    expect($preDelivery['hasWarnings'])->toBeTrue()
        ->and(count($preDelivery['warnings']))->toBeGreaterThan(0)
        ->and($prePayment['hasWarnings'])->toBeTrue();

    PaymentMilestone::query()
        ->where('contract_id', $contract->id)
        ->update([
            'checklist_status' => 'complete',
            'payment_ready' => true,
        ]);

    $resolvedPrePayment = $evaluator->evaluatePrePayment($contract->fresh());
    expect($resolvedPrePayment['hasWarnings'])->toBeFalse();
});

it('stores audit trail and supports order transitions', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-AUDIT-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item C',
        'uom' => 'Cai',
        'quantity_awarded' => 5,
    ]);
    $snapshot->lock($actor->id);

    app(AuditLogService::class)->log(
        $actor->id,
        'TenderSnapshot',
        $snapshot->id,
        'LockTenderSnapshot',
        ['snapshot_hash' => $snapshot->snapshot_hash]
    );
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    $order = Order::query()->findOrFail($contract->order_id);
    $order->transitionTo('ConfirmContract');
    $order->transitionTo('StartExecution');

    expect($order->fresh()->state)->toBe('StartExecution')
        ->and(AuditLog::query()->where('entity_type', 'TenderSnapshot')->count())->toBeGreaterThan(0);

    /** @var Contract $runtime */
    $runtime = Contract::query()->findOrFail($contract->id);
    expect($runtime->tenderSnapshot)->not->toBeNull();
});

