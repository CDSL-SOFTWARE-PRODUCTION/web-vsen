<?php

use App\Models\Cms\Category;
use App\Models\User;
use App\Filament\Cms\Resources\CategoryResource;
use App\Filament\Cms\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Cms\Resources\CategoryResource\Pages\CreateCategory;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('can access category list', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(CategoryResource::getUrl())
        ->assertOk();
});

it('lists categories in the table', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $category = Category::factory()->create(['name' => 'Demo Category']);

    actingAs($admin);
    
    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$category])
        ->assertSee('Demo Category');
});

it('can create a category', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    actingAs($admin);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'New Category',
            'slug' => 'new-category',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});
