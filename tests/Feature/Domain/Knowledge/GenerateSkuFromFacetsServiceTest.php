<?php

use App\Domain\Knowledge\GenerateSkuFromFacetsService;
use App\Models\Knowledge\CanonicalProduct;

it('generates deterministic sku regardless of facet key order', function () {
    $svc = app(GenerateSkuFromFacetsService::class);
    $a = $svc->generate(['material' => 'polyglactin', 'needle' => 'round']);
    $b = $svc->generate(['needle' => 'round', 'material' => 'polyglactin']);
    expect($a)->toBe($b)->and($a)->toStartWith('CP-');
    expect(strlen($a))->toBeLessThanOrEqual(64);
});

it('throws when facets are empty', function () {
    app(GenerateSkuFromFacetsService::class)->generate([]);
})->throws(RuntimeException::class);

it('generates a new sku when the deterministic one already exists on another product', function () {
    $facets = ['a' => 'b'];
    $svc = app(GenerateSkuFromFacetsService::class);
    $first = $svc->generate($facets);
    CanonicalProduct::query()->create([
        'sku' => $first,
        'raw_name' => 'test',
        'spec_json' => $facets,
    ]);
    $second = $svc->generate($facets);
    expect($second)->not->toBe($first);
});

it('keeps the same sku when regenerating for the same record', function () {
    $facets = ['k' => 'v'];
    $svc = app(GenerateSkuFromFacetsService::class);
    $sku = $svc->generate($facets);
    $row = CanonicalProduct::query()->create([
        'sku' => $sku,
        'raw_name' => 'x',
        'spec_json' => $facets,
    ]);
    $again = $svc->generate($facets, $row->id);
    expect($again)->toBe($sku);
});
