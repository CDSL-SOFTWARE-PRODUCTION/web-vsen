<?php

use App\Domain\Audit\AuditLogService;
use App\Domain\Demand\CloseContractCommandService;
use App\Domain\Demand\ConfirmFulfillmentCommandService;
use App\Domain\Demand\ConfirmContractCommandService;
use App\Domain\Demand\CreateOrderFromSnapshotCommandService;
use App\Domain\Demand\StartExecutionCommandService;
use App\Domain\Execution\GateEvaluator;
use App\Domain\Execution\GateOverrideService;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Models\Demand\Order;
use App\Models\Demand\PriceList;
use App\Models\Demand\PriceListItem;
use App\Models\Demand\SalesTouchpoint;
use App\Models\Demand\TenderSnapshot;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\Ops\Contract;
use App\Models\Ops\ExecutionIssue;
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

it('records gate override audit with reason for pre delivery and pre activate', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-OVERRIDE-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item D',
        'uom' => 'Cai',
        'quantity_awarded' => 8,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $service = app(GateOverrideService::class);

    $preDeliveryOverride = $service->override($contract, 'preDelivery', 'Proceed due to customer urgency.');
    $service->writeAudit($actor->id, $contract, $preDeliveryOverride);

    $contract->update(['tender_snapshot_id' => null]);
    $preActivateOverride = $service->override($contract, 'preActivate', 'Allow startup while documents are being completed.');
    $service->writeAudit($actor->id, $contract, $preActivateOverride);

    $deliveryAudit = AuditLog::query()
        ->where('entity_type', 'Contract')
        ->where('entity_id', $contract->id)
        ->where('action', 'GateOverridePreDelivery')
        ->first();

    expect($deliveryAudit)->not->toBeNull()
        ->and($deliveryAudit->context['overrideApplied'])->toBeTrue()
        ->and($deliveryAudit->context['overrideReason'])->toBe('Proceed due to customer urgency.')
        ->and($deliveryAudit->context['hasWarnings'])->toBeTrue();

    $activateAudit = AuditLog::query()
        ->where('entity_type', 'Contract')
        ->where('entity_id', $contract->id)
        ->where('action', 'GateOverridePreActivate')
        ->first();

    expect($activateAudit)->not->toBeNull()
        ->and($activateAudit->context['overrideApplied'])->toBeTrue()
        ->and($activateAudit->context['overrideReason'])->toBe('Allow startup while documents are being completed.')
        ->and($activateAudit->context['hasWarnings'])->toBeTrue();
});

it('rejects gate override when there are no warnings', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-OVERRIDE-002',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item E',
        'uom' => 'Cai',
        'quantity_awarded' => 4,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    PaymentMilestone::query()
        ->where('contract_id', $contract->id)
        ->update([
            'checklist_status' => 'complete',
            'payment_ready' => true,
        ]);

    $service = app(GateOverrideService::class);

    expect(fn () => $service->override($contract->fresh(), 'prePayment', 'Force payment gate.'))
        ->toThrow(\InvalidArgumentException::class);
});

it('confirms contract through command service and writes order audit trail', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-CONFIRM-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item F',
        'uom' => 'Cai',
        'quantity_awarded' => 6,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $result = app(ConfirmContractCommandService::class)->handle($contract->order_id, $actor->id);

    $order = Order::query()->findOrFail($contract->order_id);

    expect($order->state)->toBe('ConfirmContract')
        ->and($order->confirmed_at)->not->toBeNull()
        ->and($result->toState)->toBe('ConfirmContract');

    $audit = AuditLog::query()
        ->where('entity_type', 'Order')
        ->where('entity_id', $order->id)
        ->where('action', 'ConfirmContractCommand')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->context['from_state'])->toBe('AwardTender')
        ->and($audit->context['to_state'])->toBe('ConfirmContract');
});

it('rejects confirm contract command when order is not in award state', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-CONFIRM-002',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item G',
        'uom' => 'Cai',
        'quantity_awarded' => 6,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $order = Order::query()->findOrFail($contract->order_id);
    $order->transitionTo('ConfirmContract');

    expect(fn () => app(ConfirmContractCommandService::class)->handle($order->id, $actor->id))
        ->toThrow(\RuntimeException::class);
});

it('confirms contract with warnings and syncs contract projection counters', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-CONFIRM-003',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item H',
        'uom' => 'Cai',
        'quantity_awarded' => 6,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    ExecutionIssue::query()->create([
        'contract_id' => $contract->id,
        'issue_type' => 'Quality',
        'severity' => 'Medium',
        'status' => 'Open',
        'description' => 'Quality check pending.',
    ]);

    $result = app(ConfirmContractCommandService::class)->handle($contract->order_id, $actor->id);
    $runtimeContract = Contract::query()->findOrFail($contract->id);

    expect($result->warningRaised)->toBeTrue()
        ->and(count($result->warnings))->toBeGreaterThan(0)
        ->and($runtimeContract->risk_level)->toBe('Amber')
        ->and($runtimeContract->missing_docs_count)->toBeGreaterThan(0)
        ->and($runtimeContract->open_issues_count)->toBeGreaterThan(0);
});

it('runs full transition commands matrix for valid and invalid states', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-FLOW-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item I',
        'uom' => 'Cai',
        'quantity_awarded' => 2,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $orderId = (int) $contract->order_id;

    app(ConfirmContractCommandService::class)->handle($orderId, $actor->id);
    app(StartExecutionCommandService::class)->handle($orderId, $actor->id);
    app(ConfirmFulfillmentCommandService::class)->handle($orderId, $actor->id);
    app(CloseContractCommandService::class)->handle($orderId, $actor->id);

    expect(Order::query()->findOrFail($orderId)->state)->toBe('ContractClosed');
    expect(fn () => app(StartExecutionCommandService::class)->handle($orderId, $actor->id))
        ->toThrow(\RuntimeException::class);
});

it('returns existing runtime when generating plan twice for same snapshot', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-IDEMP-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item J',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);

    $first = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $second = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    expect($second->id)->toBe($first->id)
        ->and(Contract::query()->where('tender_snapshot_id', $snapshot->id)->count())->toBe(1)
        ->and(Order::query()->where('tender_snapshot_id', $snapshot->id)->count())->toBe(1);
});

it('creates order and lines from locked snapshot via command service', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-ORDER-CMD-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item K',
        'uom' => 'Bo',
        'quantity_awarded' => 7,
    ]);
    $snapshot->lock($actor->id);

    $result = app(CreateOrderFromSnapshotCommandService::class)->handle($snapshot->fresh('items'), $actor->id);
    $order = Order::query()->findOrFail($result->orderId);

    expect($order->state)->toBe('AwardTender')
        ->and($order->items()->count())->toBe(1)
        ->and($result->orderItemsCount)->toBe(1);

    $audit = AuditLog::query()
        ->where('entity_type', 'TenderSnapshot')
        ->where('entity_id', $snapshot->id)
        ->where('action', 'AwardTenderCommand')
        ->first();
    expect($audit)->not->toBeNull();
    expect(SalesTouchpoint::query()->where('order_id', $order->id)->count())->toBe(1);
});

it('rejects create order command when snapshot is unlocked', function () {
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-ORDER-CMD-002',
    ]);

    expect(fn () => app(CreateOrderFromSnapshotCommandService::class)->handle($snapshot->fresh('items')))
        ->toThrow(\RuntimeException::class);
});

it('blocks direct state updates outside transition services', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-BYPASS-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item M',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $order = Order::query()->findOrFail($contract->order_id);

    $order->state = 'ContractClosed';
    expect(fn () => $order->save())->toThrow(\RuntimeException::class);
});

it('raises pricing deviation warning on confirm contract when line price diverges from price list', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-PRICE-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item L',
        'uom' => 'Cai',
        'quantity_awarded' => 2,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    $priceList = PriceList::query()->create([
        'name' => 'Tender Base Price',
        'channel' => 'Tender',
    ]);
    $priceItem = PriceListItem::query()->create([
        'price_list_id' => $priceList->id,
        'product_name' => 'Item L',
        'unit_price' => 100,
        'min_qty' => 1,
        'currency' => 'VND',
    ]);

    $order = Order::query()->findOrFail($contract->order_id);
    $orderItem = $order->items()->firstOrFail();
    $orderItem->update([
        'price_list_item_id' => $priceItem->id,
        'unit_price' => 130,
    ]);

    $result = app(ConfirmContractCommandService::class)->handle($order->id, $actor->id);
    expect($result->warningRaised)->toBeTrue()
        ->and(collect($result->warnings)->contains(fn (string $warning): bool => str_contains($warning, 'Price deviation line')))->toBeTrue();
});

