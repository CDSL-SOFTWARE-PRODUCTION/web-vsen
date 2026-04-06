<?php

use App\Models\Cms\CmsProduct;

it('extracts primary image url from multiple images array', function () {
    $product = new CmsProduct([
        'images' => ['products/car.jpg', 'products/inside.jpg']
    ]);

    expect($product->primary_image_url)->toBe('products/car.jpg');
});

it('returns null for primary image when images array is empty', function () {
    $product = new CmsProduct([
        'images' => []
    ]);

    expect($product->primary_image_url)->toBeNull();
});

it('returns null for primary image when images attribute is null', function () {
    $product = new CmsProduct();
    $product->images = null;

    expect($product->primary_image_url)->toBeNull();
});

it('validates casting of is_featured as boolean', function () {
    $product = new CmsProduct([
        'is_featured' => 1,
    ]);

    expect($product->is_featured)->toBeTrue();
});
