<?php

use App\Models\Demand\Order;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['ops.driver_api_token' => 'test-driver-token']);
});

it('returns 503 when driver api token is not configured', function (): void {
    config(['ops.driver_api_token' => null]);

    $this->getJson('/api/ops/driver/deliveries/1')->assertStatus(503);
});

it('returns 401 without valid driver token', function (): void {
    $this->getJson('/api/ops/driver/deliveries/1')->assertStatus(401);
});

it('returns delivery json with valid token', function (): void {
    $order = Order::query()->create([
        'order_code' => 'ORD-API-1',
        'name' => 'API test order',
        'state' => 'StartExecution',
    ]);
    $contract = Contract::query()->create([
        'order_id' => $order->id,
        'contract_code' => 'CT-API-1',
        'name' => 'C',
        'customer_name' => 'X',
    ]);
    $delivery = Delivery::query()->create([
        'order_id' => $order->id,
        'contract_id' => $contract->id,
        'status' => 'InTransit',
        'expected_gps_coordinates' => '10,106',
    ]);

    $this->withHeader('X-Ops-Driver-Token', 'test-driver-token')
        ->getJson('/api/ops/driver/deliveries/'.$delivery->id)
        ->assertOk()
        ->assertJsonPath('id', $delivery->id);
});
