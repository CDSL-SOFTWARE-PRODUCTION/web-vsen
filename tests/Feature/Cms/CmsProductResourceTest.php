<?php

use App\Models\Cms\CmsProduct;
use App\Models\Cms\Category;
use App\Models\User;
use App\Filament\Cms\Resources\CmsProductResource;
use App\Filament\Cms\Resources\CmsProductResource\Pages\ListCmsProducts;
use App\Filament\Cms\Resources\CmsProductResource\Pages\CreateCmsProduct;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('can access product list', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(CmsProductResource::getUrl())
        ->assertOk();
});

it('lists products in the table', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $product = CmsProduct::factory()->create(['name' => 'Fancy Product']);

    actingAs($admin);
    
    livewire(ListCmsProducts::class)
        ->assertCanSeeTableRecords([$product])
        ->assertSee('Fancy Product');
});

it('can create a product', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $category = Category::factory()->create();
    actingAs($admin);

    livewire(CreateCmsProduct::class)
        ->fillForm([
            'sku' => 'SKU-001',
            'name' => 'Epic Product',
            'slug' => 'epic-product',
            'category_id' => $category->id,
            'description' => 'Product description...',
            'price' => '99.99',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'sku' => 'SKU-001',
        'name' => 'Epic Product',
    ]);
});

describe('Product Negative Tests', function () {

    it('fails when SKU is missing', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        actingAs($admin);

        livewire(CreateCmsProduct::class)
            ->fillForm([
                'name' => 'Product No SKU',
                'slug' => 'product-no-sku',
            ])
            ->call('create')
            ->assertHasFormErrors(['sku' => 'required']);
    });

    it('fails when SKU is duplicate', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        CmsProduct::factory()->create(['sku' => 'SKU-DUP']);
        
        actingAs($admin);

        livewire(CreateCmsProduct::class)
            ->fillForm([
                'sku' => 'SKU-DUP',
                'name' => 'Product Duplicate',
                'slug' => 'product-duplicate',
            ])
            ->call('create')
            ->assertHasFormErrors(['sku' => 'unique']);
    });
});

