<?php

use App\Models\User;
use App\Filament\Cms\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Cms\Resources\CmsProductResource\Pages\ListCmsProducts;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Fake the public disk to avoid writing real files
    Storage::fake('public');
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('blocks malicious file types in Article featured image', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    actingAs($admin);

    // Create a fake "malicious" PHP script
    $file = UploadedFile::fake()->create('malicious.php', 100);

    livewire(CreateArticle::class)
        ->set('data.featured_image', $file)
        ->call('create')
        ->assertHasFormErrors(['featured_image']); // Filament should catch this due to ->image()
});

it('blocks non-image files in Product images', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    actingAs($admin);

    $file = UploadedFile::fake()->create('document.pdf', 500);

    livewire(ListCmsProducts::class)
        ->mountAction('create')
        ->setActionData(['images' => [$file]])
        ->callMountedAction()
        ->assertHasActionErrors(['images']);
});

it('can upload multiple valid images to Product', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $category = \App\Models\Cms\Category::factory()->create();
    actingAs($admin);

    $file1 = UploadedFile::fake()->image('image1.jpg');
    $file2 = UploadedFile::fake()->image('image2.jpg');

    livewire(ListCmsProducts::class)
        ->mountAction('create')
        ->setActionData([
            'sku' => 'SKU-001',
            'name' => 'Product with Images',
            'slug' => 'product-with-images',
            'category_id' => $category->id,
            'is_active' => true,
            'images' => [$file1, $file2],
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    // Verify files were "saved" to fake storage
    $product = \App\Models\Cms\CmsProduct::where('sku', 'SKU-001')->first();
    
    expect($product->images)->toHaveCount(2);
    foreach($product->images as $path) {
        Storage::disk('public')->assertExists($path);
    }
});
