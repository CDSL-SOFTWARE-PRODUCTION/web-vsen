<?php

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Login;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

dataset('panelAccessMatrix', [
    ['Admin_PM', 'cms', true],
    ['Admin_PM', 'ops', true],
    ['Sale',     'cms', false],
    ['Sale',     'ops', true],
    ['Kho',      'cms', false],
    ['Kho',      'ops', true],
    ['KeToan',   'cms', false],
    ['KeToan',   'ops', true],
    ['MuaHang',  'cms', false],
    ['MuaHang',  'ops', true],
    ['user',     'cms', false],
    ['user',     'ops', false],
]);

it('enforces panel access matrix correctly', function (string $role, string $panelId, bool $shouldHaveAccess) {
    $user = User::factory()->create(['role' => $role]);
    
    // Some panel access methods use the current panel from the facade.
    Filament::setCurrentPanel(Filament::getPanel($panelId));
    
    $url = '/' . Filament::getPanel($panelId)->getPath();

    $request = actingAs($user)->get($url);

    if ($shouldHaveAccess) {
        $request->assertOk();
    } else {
        $request->assertForbidden();
    }
})->with('panelAccessMatrix');

it('can login to CMS panel with correct credentials', function () {
    $admin = User::factory()->create([
        'role' => 'Admin_PM',
        'password' => bcrypt('test-password'),
    ]);

    Filament::setCurrentPanel(Filament::getPanel('cms'));

    get('/cms/login')->assertOk();

    livewire(Login::class)
        ->fillForm([
            'email' => $admin->email,
            'password' => 'test-password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect('/cms');

    $this->assertAuthenticatedAs($admin);
});

it('can login to OPS panel with correct credentials', function () {
    $saleUser = User::factory()->create([
        'role' => 'Sale',
        'password' => bcrypt('test-password'),
    ]);

    Filament::setCurrentPanel(Filament::getPanel('ops'));

    get('/ops/login')->assertOk();

    livewire(Login::class)
        ->fillForm([
            'email' => $saleUser->email,
            'password' => 'test-password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect('/ops');

    $this->assertAuthenticatedAs($saleUser);
});

it('fails login with invalid password', function () {
    $user = User::factory()->create();
    
    Filament::setCurrentPanel(Filament::getPanel('cms'));

    livewire(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);
});
