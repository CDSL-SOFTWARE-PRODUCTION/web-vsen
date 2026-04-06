<?php

use App\Models\Cms\Contact;
use App\Models\User;
use App\Filament\Cms\Resources\ContactResource;
use App\Filament\Cms\Resources\ContactResource\Pages\ListContacts;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('cms'));
});

it('can access contact list', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(ContactResource::getUrl())
        ->assertOk();
});

it('lists contacts in the table', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    $contact = Contact::factory()->create(['name' => 'John Requester']);

    actingAs($admin);
    
    livewire(ListContacts::class)
        ->assertCanSeeTableRecords([$contact])
        ->assertSee('John Requester');
});
