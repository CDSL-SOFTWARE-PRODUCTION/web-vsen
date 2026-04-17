<?php

use App\Domain\Audit\AuditLogService;
use App\Domain\Demand\CloseContractCommandService;
use App\Domain\Demand\ConfirmContractCommandService;
use App\Domain\Demand\ConfirmFulfillmentCommandService;
use App\Domain\Demand\CreateOrderFromSnapshotCommandService;
use App\Domain\Demand\StartExecutionCommandService;
use App\Domain\Execution\GateEvaluator;
use App\Domain\Execution\GateOverrideService;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Domain\Finance\CancelAndReissueInvoiceService;
use App\Domain\Finance\IssueInvoiceService;
use App\Domain\Finance\MilestoneAgingService;
use App\Domain\Supply\GenerateSupplyOrderFromOrderService;
use App\Domain\Supply\ProcessReturnOrderService;
use App\Domain\Supply\ReceiveSupplyOrderService;
use App\Domain\Supply\ReserveInventoryService;
use App\Domain\Supply\StockTransferService;
use App\Models\Demand\Order;
use App\Models\Demand\PriceList;
use App\Models\Demand\PriceListItem;
use App\Models\Demand\SalesTouchpoint;
use App\Models\Demand\TenderSnapshot;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\LegalEntity;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use App\Models\Ops\Document;
use App\Models\Ops\ExecutionIssue;
use App\Models\Ops\FinancialLedgerEntry;
use App\Models\Ops\Invoice;
use App\Models\Ops\Partner;
use App\Models\Ops\PaymentMilestone;
use App\Models\Supply\InventoryLedger;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\InventoryReservation;
use App\Models\Supply\ReturnLineItem;
use App\Models\Supply\ReturnOrder;
use App\Models\Supply\StockTransfer;
use App\Models\Supply\SupplyOrder;
use App\Models\System\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['ops.gates.confirm_fulfillment' => 'warn']);
});

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
        ->toThrow(InvalidArgumentException::class);
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
        ->toThrow(RuntimeException::class);
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
        ->toThrow(RuntimeException::class);
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
        ->toThrow(RuntimeException::class);
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
    expect(fn () => $order->save())->toThrow(RuntimeException::class);
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

it('creates supply order lines for shortage items only', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-SUP-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item N',
        'uom' => 'Cai',
        'quantity_awarded' => 10,
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 2,
        'name' => 'Item O',
        'uom' => 'Cai',
        'quantity_awarded' => 5,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    InventoryLot::query()->create([
        'item_name' => 'Item O',
        'available_qty' => 9,
    ]);

    $result = app(GenerateSupplyOrderFromOrderService::class)->handle((int) $contract->order_id, $actor->id);
    $supplyOrder = SupplyOrder::query()->findOrFail((int) $result->supplyOrderId);

    expect($result->shortageLinesCount)->toBe(1)
        ->and($supplyOrder->lines()->count())->toBe(1)
        ->and($supplyOrder->lines()->firstOrFail()->item_name)->toBe('Item N');
});

it('skips supply order generation when inventory is sufficient', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-SUP-002',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item P',
        'uom' => 'Cai',
        'quantity_awarded' => 4,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    InventoryLot::query()->create([
        'item_name' => 'Item P',
        'available_qty' => 10,
    ]);

    $result = app(GenerateSupplyOrderFromOrderService::class)->handle((int) $contract->order_id, $actor->id);
    expect($result->supplyOrderId)->toBeNull()
        ->and($result->shortageLinesCount)->toBe(0);
});

it('receives supply order and appends inventory ledger entries', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-SUP-003',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item Q',
        'uom' => 'Cai',
        'quantity_awarded' => 6,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $generated = app(GenerateSupplyOrderFromOrderService::class)->handle((int) $contract->order_id, $actor->id);
    $supplyOrderId = (int) $generated->supplyOrderId;

    $received = app(ReceiveSupplyOrderService::class)->handle($supplyOrderId, $actor->id);
    $lot = InventoryLot::query()->where('item_name', 'Item Q')->firstOrFail();
    $ledger = InventoryLedger::query()
        ->where('supply_order_id', $supplyOrderId)
        ->where('item_name', 'Item Q')
        ->firstOrFail();
    $supplyOrder = SupplyOrder::query()->findOrFail($supplyOrderId);

    expect($received->receivedLinesCount)->toBe(1)
        ->and((float) $lot->available_qty)->toBe(6.0)
        ->and($ledger->action)->toBe('IN')
        ->and((float) $ledger->qty_change)->toBe(6.0)
        ->and((float) $ledger->balance_after)->toBe(6.0)
        ->and($supplyOrder->status)->toBe('Received')
        ->and($supplyOrder->lines()->firstOrFail()->status)->toBe('Received');
});

it('reserves inventory, prevents over reserve, and supports release flow', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-RES-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item R',
        'uom' => 'Cai',
        'quantity_awarded' => 3,
    ]);
    $snapshot->lock($actor->id);

    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $order = Order::query()->findOrFail((int) $contract->order_id);
    $orderItem = $order->items()->firstOrFail();

    $lot = InventoryLot::query()->create([
        'item_name' => 'Item R',
        'available_qty' => 5,
    ]);

    $service = app(ReserveInventoryService::class);
    $reserved = $service->reserve($orderItem->id, $lot->id, 3, $actor->id);

    $lot->refresh();
    $reservation = InventoryReservation::query()->findOrFail($reserved->reservationId);
    $reserveLedger = InventoryLedger::query()
        ->where('inventory_lot_id', $lot->id)
        ->where('action', 'RESERVE')
        ->latest('id')
        ->firstOrFail();

    expect((float) $lot->available_qty)->toBe(2.0)
        ->and((float) $reservation->reserved_qty)->toBe(3.0)
        ->and((float) $reserveLedger->qty_change)->toBe(-3.0);

    expect(fn () => $service->reserve($orderItem->id, $lot->id, 3, $actor->id))
        ->toThrow(RuntimeException::class);

    $service->release($reservation->id, $actor->id);
    $lot->refresh();
    $reservation->refresh();
    $releaseLedger = InventoryLedger::query()
        ->where('inventory_lot_id', $lot->id)
        ->where('action', 'RELEASE')
        ->latest('id')
        ->firstOrFail();

    expect((float) $lot->available_qty)->toBe(5.0)
        ->and($reservation->status)->toBe('Released')
        ->and((float) $releaseLedger->qty_change)->toBe(3.0);
});

it('ships and receives stock transfer between warehouses with ledger updates', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $sourceLot = InventoryLot::query()->create([
        'item_name' => 'Item S',
        'warehouse_code' => 'WH-A',
        'available_qty' => 8,
    ]);

    $service = app(StockTransferService::class);
    $shipped = $service->ship('Item S', 'WH-A', 'WH-B', 5, $actor->id);
    $transfer = StockTransfer::query()->findOrFail($shipped->transferId);
    $sourceLot->refresh();

    $outLedger = InventoryLedger::query()
        ->where('inventory_lot_id', $sourceLot->id)
        ->where('action', 'TRANSFER_OUT')
        ->latest('id')
        ->firstOrFail();

    expect($transfer->status)->toBe('Shipped')
        ->and((float) $sourceLot->available_qty)->toBe(3.0)
        ->and((float) $outLedger->qty_change)->toBe(-5.0);

    $received = $service->receive($transfer->id, $actor->id);
    $transfer->refresh();
    $destLot = InventoryLot::query()
        ->where('item_name', 'Item S')
        ->where('warehouse_code', 'WH-B')
        ->firstOrFail();
    $inLedger = InventoryLedger::query()
        ->where('inventory_lot_id', $destLot->id)
        ->where('action', 'TRANSFER_IN')
        ->latest('id')
        ->firstOrFail();

    expect($received->status)->toBe('Received')
        ->and($transfer->status)->toBe('Received')
        ->and((float) $destLot->available_qty)->toBe(5.0)
        ->and((float) $inLedger->qty_change)->toBe(5.0);
});

it('blocks stock transfer when source warehouse has no matching lot', function () {
    $service = app(StockTransferService::class);
    expect(fn () => $service->ship('Item T', 'WH-EMPTY', 'WH-B', 1))
        ->toThrow(RuntimeException::class);
});

it('processes return order for restock and dispose paths', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);

    $returnOrder = ReturnOrder::query()->create([
        'return_code' => 'RO-001',
        'status' => 'Approved',
    ]);
    ReturnLineItem::query()->create([
        'return_order_id' => $returnOrder->id,
        'item_name' => 'Item U',
        'warehouse_code' => 'WH-A',
        'quantity' => 4,
        'condition' => 'Good',
    ]);
    ReturnLineItem::query()->create([
        'return_order_id' => $returnOrder->id,
        'item_name' => 'Item V',
        'warehouse_code' => 'WH-A',
        'quantity' => 2,
        'condition' => 'Defective',
    ]);

    $result = app(ProcessReturnOrderService::class)->handle($returnOrder->id, $actor->id);
    $returnOrder->refresh();

    $restockLot = InventoryLot::query()
        ->where('item_name', 'Item U')
        ->where('warehouse_code', 'WH-A')
        ->firstOrFail();
    $restockLedger = InventoryLedger::query()
        ->where('item_name', 'Item U')
        ->where('action', 'RESTOCK')
        ->latest('id')
        ->firstOrFail();
    $disposeLedger = InventoryLedger::query()
        ->where('item_name', 'Item V')
        ->where('action', 'DISPOSE')
        ->latest('id')
        ->firstOrFail();

    expect($result->restockedLinesCount)->toBe(1)
        ->and($result->disposedLinesCount)->toBe(1)
        ->and($returnOrder->status)->toBe('Processing')
        ->and((float) $restockLot->available_qty)->toBe(4.0)
        ->and((float) $restockLedger->qty_change)->toBe(4.0)
        ->and((float) $disposeLedger->qty_change)->toBe(-2.0);
});

it('rejects issue invoice when delivery or acceptance proof is missing', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-INV-FAIL-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item INV',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    expect(fn () => app(IssueInvoiceService::class)->handle((int) $contract->id, 1_000_000.0, $actor->id))
        ->toThrow(RuntimeException::class);
});

it('rejects issue invoice when payment milestone gate is hard and checklist incomplete', function () {
    config(['ops.gates.invoice_payment_milestone' => 'hard']);

    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-INV-MILE-HARD-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item INV-MH',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    Delivery::query()->create([
        'order_id' => $contract->order_id,
        'contract_id' => $contract->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
    ]);

    Document::query()
        ->where('contract_id', $contract->id)
        ->where('document_type', 'Acceptance Minute')
        ->update(['status' => 'uploaded']);

    expect(fn () => app(IssueInvoiceService::class)->handle((int) $contract->id, 1_000_000.0, $actor->id))
        ->toThrow(RuntimeException::class);
});

it('rejects issue invoice when shipment exists but is not Delivered', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-INV-NOT-DEL-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item INV-ND',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    Delivery::query()->create([
        'order_id' => $contract->order_id,
        'contract_id' => $contract->id,
        'status' => 'InTransit',
        'dispatched_at' => now(),
    ]);

    Document::query()
        ->where('contract_id', $contract->id)
        ->where('document_type', 'Acceptance Minute')
        ->update(['status' => 'uploaded']);

    expect(fn () => app(IssueInvoiceService::class)->handle((int) $contract->id, 1_000_000.0, $actor->id))
        ->toThrow(RuntimeException::class);
});

it('issues invoice and ledger inflow when delivery is delivered and acceptance minute is present', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-INV-OK-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item INV2',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    Delivery::query()->create([
        'order_id' => $contract->order_id,
        'contract_id' => $contract->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
    ]);

    Document::query()
        ->where('contract_id', $contract->id)
        ->where('document_type', 'Acceptance Minute')
        ->update(['status' => 'uploaded']);

    PaymentMilestone::query()
        ->where('contract_id', $contract->id)
        ->update([
            'checklist_status' => 'complete',
            'payment_ready' => true,
        ]);

    $invoice = app(IssueInvoiceService::class)->handle((int) $contract->id, 2_500_000.0, $actor->id);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->status)->toBe('Issued')
        ->and(FinancialLedgerEntry::query()->where('invoice_id', $invoice->id)->where('type', 'Inflow')->exists())->toBeTrue();
});

it('surfaces confirm fulfillment readiness warnings when delivery or acceptance is missing', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-FF-READINESS-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item FF',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    $readiness = app(GateEvaluator::class)->evaluateConfirmFulfillmentReadiness($contract->fresh());

    expect($readiness['hasWarnings'])->toBeTrue()
        ->and(count($readiness['warnings']))->toBeGreaterThan(0);
});

it('refreshes milestone overdue cache', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-AGING-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item AG',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    $milestone = PaymentMilestone::query()->where('contract_id', $contract->id)->firstOrFail();
    $milestone->update(['due_date' => now()->subDays(5)->toDateString()]);

    $updated = app(MilestoneAgingService::class)->refreshAllCachedOverdue(now());

    $milestone->refresh();
    expect($updated)->toBeGreaterThan(0)
        ->and($milestone->days_overdue_cached)->toBeGreaterThan(0);
});

it('blocks confirm fulfillment when hard gate enabled and delivery missing', function () {
    config(['ops.gates.confirm_fulfillment' => 'hard']);

    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-HARD-FF-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item HARD',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $orderId = (int) $contract->order_id;

    app(ConfirmContractCommandService::class)->handle($orderId, $actor->id);
    app(StartExecutionCommandService::class)->handle($orderId, $actor->id);

    expect(fn () => app(ConfirmFulfillmentCommandService::class)->handle($orderId, $actor->id))
        ->toThrow(RuntimeException::class);
});

it('scopes orders by legal entity for non-admin users', function () {
    $le1 = LegalEntity::query()->create(['name' => 'Entity One']);
    $le2 = LegalEntity::query()->create(['name' => 'Entity Two']);

    $user = User::factory()->create([
        'role' => 'KeToan',
        'legal_entity_id' => $le2->id,
    ]);

    $orderOther = Order::query()->withoutGlobalScopes()->create([
        'legal_entity_id' => $le1->id,
        'order_code' => 'ORD-SCOPE-A-'.uniqid(),
        'name' => 'Other LE',
        'state' => 'AwardTender',
    ]);

    $orderMine = Order::query()->withoutGlobalScopes()->create([
        'legal_entity_id' => $le2->id,
        'order_code' => 'ORD-SCOPE-B-'.uniqid(),
        'name' => 'My LE',
        'state' => 'AwardTender',
    ]);

    actingAs($user);

    $visibleIds = Order::query()->pluck('id')->all();

    expect($visibleIds)->toContain($orderMine->id)
        ->and($visibleIds)->not->toContain($orderOther->id);
});

it('blocks confirm contract when customer overdue exceeds threshold C-ORD-005', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-CORD5-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item C5',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);
    $orderId = (int) $contract->order_id;

    $customer = Partner::query()->create([
        'name' => 'Overdue Customer',
        'type' => 'Customer',
        'max_overdue_days_cached' => 45,
    ]);
    $contract->update(['customer_partner_id' => $customer->id]);

    expect(fn () => app(ConfirmContractCommandService::class)->handle($orderId, $actor->id))
        ->toThrow(RuntimeException::class);
});

it('voids and replaces invoice via CancelAndReissue C-FIN-002', function () {
    $actor = User::factory()->create(['role' => 'Admin_PM']);
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-REISSUE-001',
    ]);
    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Item RE',
        'uom' => 'Cai',
        'quantity_awarded' => 1,
    ]);
    $snapshot->lock($actor->id);
    $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

    Delivery::query()->create([
        'order_id' => $contract->order_id,
        'contract_id' => $contract->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
    ]);

    Document::query()
        ->where('contract_id', $contract->id)
        ->where('document_type', 'Acceptance Minute')
        ->update(['status' => 'uploaded']);

    PaymentMilestone::query()
        ->where('contract_id', $contract->id)
        ->update([
            'checklist_status' => 'complete',
            'payment_ready' => true,
        ]);

    $invoice = app(IssueInvoiceService::class)->handle((int) $contract->id, 1_000_000.0, $actor->id);

    $replacement = app(CancelAndReissueInvoiceService::class)->handle((int) $invoice->id, 900_000.0, $actor->id);

    $invoice->refresh();

    expect($invoice->status)->toBe('Voided')
        ->and($invoice->replaced_by_invoice_id)->toBe($replacement->id)
        ->and($replacement->status)->toBe('Issued')
        ->and((float) $replacement->total_amount)->toBe(900_000.0);
});
