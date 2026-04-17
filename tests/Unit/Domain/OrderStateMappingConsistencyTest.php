<?php

use App\Domain\Demand\OrderState;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

uses(TestCase::class);

it('keeps OrderState runtime map in sync with model/order_state_mapping.yaml', function (): void {
    $path = base_path('model/order_state_mapping.yaml');
    expect(file_exists($path))->toBeTrue();

    $data = Yaml::parseFile($path);
    expect($data)->toBeArray()->toHaveKey('mapping');

    $fromYaml = [];
    foreach ($data['mapping'] as $row) {
        expect($row)->toHaveKeys(['runtime', 'canonical']);
        $fromYaml[$row['runtime']] = $row['canonical'];
    }

    $fromClass = [];
    foreach (OrderState::allRuntimeStates() as $runtime) {
        $fromClass[$runtime] = OrderState::runtimeToCanonical($runtime);
    }

    expect($fromYaml)->toBe($fromClass);
});

it('maps every runtime state to a canonical name present in model states registry', function (): void {
    $statesPath = base_path('model/states.yaml');
    $doc = Yaml::parseFile($statesPath);
    $orderMachine = collect($doc['state_machines'] ?? [])
        ->firstWhere('entity', 'Order');
    expect($orderMachine)->not->toBeNull();

    $canonicalSet = collect($orderMachine['states'] ?? [])->flip()->all();

    foreach (OrderState::allRuntimeStates() as $runtime) {
        $canonical = OrderState::runtimeToCanonical($runtime);
        expect($canonicalSet)->toHaveKey($canonical);
    }
});
