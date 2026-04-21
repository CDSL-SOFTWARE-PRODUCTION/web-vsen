<?php

use App\Domain\Supply\SupplyOrderGateService;
use App\Models\Demand\Order;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\LegalEntity;
use App\Models\Ops\Partner;
use App\Models\Supply\SupplyOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

function makeSupplyOrderWithLineForGate(?int $supplierId, ?int $canonicalId, ?float $priceDeviationPct): SupplyOrder
{
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-GATE-'.uniqid('', true)]);

    $order = Order::query()->create([
        'legal_entity_id' => $legalEntity->id,
        'order_code' => 'ORD-GATE-'.uniqid('', true),
        'name' => 'Gate test',
        'state' => 'AwardTender',
    ]);

    $orderItem = $order->items()->create([
        'line_no' => 1,
        'name' => 'Line',
        'quantity' => 1,
        'status' => 'planned',
        'procurement_status' => 'pending',
        'canonical_product_id' => $canonicalId,
        'unit_price' => 100000,
    ]);

    $supplyOrder = SupplyOrder::query()->create([
        'order_id' => $order->id,
        'supply_order_code' => 'SO-GATE-'.uniqid('', true),
        'status' => 'Draft',
    ]);

    $supplyOrder->lines()->create([
        'order_item_id' => $orderItem->id,
        'canonical_product_id' => $canonicalId,
        'supplier_partner_id' => $supplierId,
        'item_name' => 'Line',
        'required_qty' => 1,
        'available_qty' => 0,
        'shortage_qty' => 1,
        'price_deviation_pct' => $priceDeviationPct,
    ]);

    return $supplyOrder->fresh()->load('lines');
}

it('warns when supply order has no lines', function (): void {
    $legalEntity = LegalEntity::query()->create(['name' => 'LE-EMPTY']);
    $order = Order::query()->create([
        'legal_entity_id' => $legalEntity->id,
        'order_code' => 'ORD-EMPTY-1',
        'name' => 'Empty SO',
        'state' => 'AwardTender',
    ]);
    $supplyOrder = SupplyOrder::query()->create([
        'order_id' => $order->id,
        'supply_order_code' => 'SO-EMPTY-1',
        'status' => 'Draft',
    ]);
    $supplyOrder->load('lines');

    $warnings = app(SupplyOrderGateService::class)->evaluate($supplyOrder);

    expect($warnings)->not->toBeEmpty()
        ->and(implode(' ', $warnings))->toContain('at least one line');
});

it('warns when a line has no supplier', function (): void {
    $canonical = CanonicalProduct::query()->create([
        'sku' => 'SKU-GATE-1',
        'raw_name' => 'P1',
    ]);
    $supplyOrder = makeSupplyOrderWithLineForGate(null, $canonical->id, null);

    $warnings = app(SupplyOrderGateService::class)->evaluate($supplyOrder);

    expect($warnings)->not->toBeEmpty()
        ->and(implode(' ', $warnings))->toContain('supplier');
});

it('warns when price deviation exceeds configured hard percent', function (): void {
    config(['ops.supply_order_price_deviation_hard_percent' => 10]);

    $canonical = CanonicalProduct::query()->create([
        'sku' => 'SKU-GATE-2',
        'raw_name' => 'P2',
    ]);
    $supplier = Partner::query()->create([
        'name' => 'Sup',
        'type' => 'Supplier',
    ]);
    $supplyOrder = makeSupplyOrderWithLineForGate($supplier->id, $canonical->id, 15.0);

    $warnings = app(SupplyOrderGateService::class)->evaluate($supplyOrder);

    expect($warnings)->not->toBeEmpty()
        ->and(implode(' ', $warnings))->toContain('price deviation');
});

it('passes evaluate when line is complete and deviation within threshold', function (): void {
    config(['ops.supply_order_price_deviation_hard_percent' => 10]);

    $canonical = CanonicalProduct::query()->create([
        'sku' => 'SKU-GATE-3',
        'raw_name' => 'P3',
    ]);
    $supplier = Partner::query()->create([
        'name' => 'Sup2',
        'type' => 'Supplier',
    ]);
    $supplyOrder = makeSupplyOrderWithLineForGate($supplier->id, $canonical->id, 5.0);

    $warnings = app(SupplyOrderGateService::class)->evaluate($supplyOrder);

    expect($warnings)->toBe([]);
});
