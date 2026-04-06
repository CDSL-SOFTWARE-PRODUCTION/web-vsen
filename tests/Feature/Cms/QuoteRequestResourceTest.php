<?php

use App\Models\Cms\QuoteRequest;
use App\Models\Cms\CmsProduct;
use App\Models\User;
use App\Filament\Cms\Resources\QuoteRequestResource;
use App\Filament\Cms\Resources\QuoteRequestResource\Pages\ListQuoteRequests;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('can access quote request list', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(QuoteRequestResource::getUrl())
        ->assertOk();
});

it('lists quote requests in the table', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $quoteRequest = QuoteRequest::factory()->create(['name' => 'Alice Quote']);

    actingAs($admin);
    
    livewire(ListQuoteRequests::class)
        ->assertCanSeeTableRecords([$quoteRequest])
        ->assertSee('Alice Quote');
});
