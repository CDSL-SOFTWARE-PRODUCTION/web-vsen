<?php

use App\Models\User;
use App\Models\Cms\Article;
use App\Models\Cms\Category;
use App\Models\Cms\CmsProduct;
use App\Filament\Cms\Resources\ArticleResource;
use App\Filament\Cms\Resources\CategoryResource;
use App\Filament\Cms\Resources\CmsProductResource;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

describe('CMS Access Control', function () {
    
    it('redirects guests to login', function ($url) {
        get($url)->assertRedirect('/cms/login');
    })->with([
        fn() => ArticleResource::getUrl(),
        fn() => CategoryResource::getUrl(),
        fn() => CmsProductResource::getUrl(),
    ]);

    it('denies access to non-admin roles', function ($role) {
        $user = User::factory()->create(['role' => $role]);

        actingAs($user)
            ->get(ArticleResource::getUrl())
            ->assertForbidden();
    })->with(['Sale', 'Kho', 'KeToan', 'MuaHang', 'user']);

    it('allows Admin_PM to access all CMS resources', function ($url) {
        $admin = User::factory()->create(['role' => 'Admin_PM']);

        actingAs($admin)
            ->get($url)
            ->assertOk();
    })->with([
        fn() => ArticleResource::getUrl(),
        fn() => CategoryResource::getUrl(),
        fn() => CmsProductResource::getUrl(),
    ]);
});

describe('CMS Data Integrity & Fault Tolerance', function () {

    it('handles product list with missing category gracefully', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        
        // Create a product where the category was deleted or is null
        $product = CmsProduct::factory()->create(['category_id' => null]);

        actingAs($admin)
            ->get(CmsProductResource::getUrl())
            ->assertOk()
            ->assertSee($product->name);
    });

    it('handles orphaned records in relations', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        $category = Category::factory()->create();
        $product = CmsProduct::factory()->create(['category_id' => $category->id]);
        
        // Simulate data corruption or force deletion without proper constraints
        $category->delete();

        actingAs($admin)
            ->get(CmsProductResource::getUrl())
            ->assertOk()
            ->assertSee($product->name);
    });
});
