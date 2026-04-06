<?php

use App\Models\Cms\Article;
use App\Models\User;
use App\Filament\Cms\Resources\ArticleResource;
use App\Filament\Cms\Resources\ArticleResource\Pages\ListArticles;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('redirects guest from cms articles', function () {
    get(ArticleResource::getUrl())
        ->assertRedirect('/cms/login');
});

it('allows admin to access article list', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(ArticleResource::getUrl())
        ->assertOk();
});

it('lists articles in the table', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $article = Article::factory()->create(['title' => 'Test Article Title']);

    actingAs($admin);
    
    livewire(ListArticles::class)
        ->assertCanSeeTableRecords([$article])
        ->assertTableColumnExists('title')
        ->assertSee('Test Article Title');
});

it('can create an article', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    actingAs($admin);

    livewire(\App\Filament\Cms\Resources\ArticleResource\Pages\CreateArticle::class)
        ->fillForm([
            'title' => 'My New Epic Article',
            'slug' => 'my-new-epic-article',
            'content' => 'Full article content here...',
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'title' => 'My New Epic Article',
        'is_published' => true,
    ]);
});

describe('Negative Validation Tests', function () {

    it('fails when title is missing', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        actingAs($admin);

        livewire(\App\Filament\Cms\Resources\ArticleResource\Pages\CreateArticle::class)
            ->fillForm([
                'slug' => 'test-slug',
                'content' => 'Content...',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    });

    it('fails when slug is duplicate', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        Article::factory()->create(['slug' => 'taken-slug']);
        actingAs($admin);

        livewire(\App\Filament\Cms\Resources\ArticleResource\Pages\CreateArticle::class)
            ->fillForm([
                'title' => 'New Article',
                'slug' => 'taken-slug',
                'content' => 'Content...',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    });

    it('fails when content is missing', function () {
        $admin = User::factory()->create(['role' => 'Admin_PM']);
        actingAs($admin);

        livewire(\App\Filament\Cms\Resources\ArticleResource\Pages\CreateArticle::class)
            ->fillForm(['title' => 'Title Only'])
            ->call('create')
            ->assertHasFormErrors(['content' => 'required']);
    });
});

