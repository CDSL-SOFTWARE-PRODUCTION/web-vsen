<?php

use App\Domain\Demand\CreateOrderFromBidOpeningSessionService;
use App\Domain\Supply\ApproveSupplyOrderService;
use App\Domain\Supply\RequestSupplyOrderApprovalService;
use App\Models\Demand\BidOpeningLine;
use App\Models\Demand\BidOpeningSession;
use App\Models\Demand\Order;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\LegalEntity;
use App\Models\Ops\Partner;
use App\Models\Supply\SupplyOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('blocks order projection when bid opening lines are not mapped', function () {
    $user = User::factory()->create(['role' => 'Admin_PM']);
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-1']);
    $user->update(['legal_entity_id' => $legalEntity->id]);

    $session = BidOpeningSession::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'IB-STRICT-001',
        'session_version' => 1,
    ]);

    BidOpeningLine::query()->create([
        'bid_opening_session_id' => $session->id,
        'source_row_no' => 1,
        'lot_code' => 'PP0001',
        'item_name' => 'Vat tu A',
        'mapping_status' => 'unmapped',
        'bidder_name' => 'NCC A',
        'bid_price' => 100000,
        'currency' => 'VND',
        'row_fingerprint' => sha1('row-1'),
    ]);

    expect(fn () => app(CreateOrderFromBidOpeningSessionService::class)->handle($session->id, $user->id))
        ->toThrow(RuntimeException::class);
});

it('creates order and supply order from fully mapped bid opening session', function () {
    $user = User::factory()->create(['role' => 'Admin_PM']);
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-2']);
    $user->update(['legal_entity_id' => $legalEntity->id]);
    $canonical = CanonicalProduct::query()->create([
        'sku' => 'PP2600114846',
        'raw_name' => 'Bang dan vo trung',
    ]);

    $session = BidOpeningSession::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'IB-STRICT-002',
        'session_version' => 1,
    ]);
    $supplier = Partner::query()->create([
        'name' => 'NCC B',
        'type' => 'Supplier',
    ]);

    BidOpeningLine::query()->create([
        'bid_opening_session_id' => $session->id,
        'source_row_no' => 1,
        'lot_code' => 'PP2600114846',
        'item_name' => 'Bang dan vo trung truoc mo',
        'canonical_product_id' => $canonical->id,
        'mapping_status' => 'mapped',
        'mapped_at' => now(),
        'bidder_name' => 'NCC B',
        'bid_price' => 120000,
        'currency' => 'VND',
        'row_fingerprint' => sha1('row-2'),
    ]);

    $result = app(CreateOrderFromBidOpeningSessionService::class)->handle($session->id, $user->id);

    $order = Order::query()->findOrFail($result['order_id']);
    $orderItem = $order->items()->firstOrFail();
    $supplyOrder = SupplyOrder::query()->where('order_id', $order->id)->firstOrFail();
    $supplyLine = $supplyOrder->lines()->firstOrFail();

    expect($result['order_items_count'])->toBe(1)
        ->and($orderItem->canonical_product_id)->toBe($canonical->id)
        ->and($supplyLine->canonical_product_id)->toBe($canonical->id)
        ->and($supplyLine->supplier_partner_id)->toBe($supplier->id)
        ->and($supplyLine->supplier_suggestion_source)->toBe('bidder_name')
        ->and($supplyLine->supplier_selection_mode)->toBe('auto_suggested')
        ->and($supplyOrder->status)->toBe('Draft');
});

it('requires supplier per line and mapped lines before approving supply order', function () {
    $user = User::factory()->create(['role' => 'Admin_PM']);
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-3']);
    $user->update(['legal_entity_id' => $legalEntity->id]);
    $canonical = CanonicalProduct::query()->create([
        'sku' => 'PP2600114847',
        'raw_name' => 'Bang dan vo trung 2',
    ]);

    $order = Order::query()->create([
        'legal_entity_id' => $legalEntity->id,
        'order_code' => 'ORD-APPR-001',
        'name' => 'Order approval gate',
        'state' => 'AwardTender',
    ]);
    $orderItem = $order->items()->create([
        'line_no' => 1,
        'name' => 'Hang gate',
        'quantity' => 1,
        'status' => 'planned',
        'procurement_status' => 'pending',
        'canonical_product_id' => $canonical->id,
        'unit_price' => 100000,
    ]);

    $supplyOrder = SupplyOrder::query()->create([
        'order_id' => $order->id,
        'supply_order_code' => 'SO-APPR-001',
        'status' => 'Draft',
    ]);
    $supplyOrder->lines()->create([
        'order_item_id' => $orderItem->id,
        'canonical_product_id' => $canonical->id,
        'item_name' => 'Hang gate',
        'required_qty' => 1,
        'available_qty' => 0,
        'shortage_qty' => 1,
    ]);

    app(RequestSupplyOrderApprovalService::class)->handle($supplyOrder->id, $user->id);
    expect($supplyOrder->fresh()->status)->toBe('PendingApproval');

    expect(fn () => app(ApproveSupplyOrderService::class)->handle($supplyOrder->id, $user->id))
        ->toThrow(RuntimeException::class);

    $supplier = Partner::query()->create([
        'name' => 'Supplier Z',
        'type' => 'Supplier',
    ]);
    $lineAfterManualSelection = $supplyOrder->lines()->firstOrFail();
    $lineAfterManualSelection->update([
        'supplier_partner_id' => $supplier->id,
    ]);
    $lineAfterManualSelection->refresh();
    expect($lineAfterManualSelection->supplier_selection_mode)->toBe('manual_override');

    app(ApproveSupplyOrderService::class)->handle($supplyOrder->id, $user->id);
    expect($supplyOrder->fresh()->status)->toBe('Approved');
});

it('maps supplier by bidder identifier when generating supply line', function () {
    $user = User::factory()->create(['role' => 'Admin_PM']);
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-4']);
    $user->update(['legal_entity_id' => $legalEntity->id]);
    $canonical = CanonicalProduct::query()->create([
        'sku' => 'PP2600114848',
        'raw_name' => 'Bang dan vo trung 3',
    ]);
    $supplier = Partner::query()->create([
        'name' => 'Supplier By Identifier',
        'type' => 'Supplier',
        'bidder_identifier' => '010203',
    ]);

    $session = BidOpeningSession::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'IB-STRICT-003',
        'session_version' => 1,
    ]);
    BidOpeningLine::query()->create([
        'bid_opening_session_id' => $session->id,
        'source_row_no' => 1,
        'lot_code' => 'PP2600114848',
        'item_name' => 'Bang dan theo ma dinh danh',
        'canonical_product_id' => $canonical->id,
        'mapping_status' => 'mapped',
        'mapped_at' => now(),
        'bidder_identifier' => '010203',
        'bidder_name' => 'Name not equal partner',
        'bid_price' => 50000,
        'currency' => 'VND',
        'row_fingerprint' => sha1('row-3'),
    ]);

    $result = app(CreateOrderFromBidOpeningSessionService::class)->handle($session->id, $user->id);

    $supplyOrder = SupplyOrder::query()->where('order_id', $result['order_id'])->firstOrFail();
    $supplyLine = $supplyOrder->lines()->firstOrFail();

    expect($supplyLine->supplier_partner_id)->toBe($supplier->id)
        ->and($supplyLine->supplier_suggestion_source)->toBe('bidder_identifier')
        ->and($supplyLine->supplier_selection_mode)->toBe('auto_suggested');
});
