<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\URL;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

dataset('panelAccessMatrix', [
    ['Admin_PM', 'cms', true],
    ['Admin_PM', 'ops', true],
    ['Sale', 'cms', false],
    ['Sale', 'ops', true],
    ['Kho', 'cms', false],
    ['Kho', 'ops', true],
    ['KeToan', 'cms', false],
    ['KeToan', 'ops', true],
    ['MuaHang', 'cms', false],
    ['MuaHang', 'ops', true],
    ['user', 'cms', false],
    ['user', 'ops', false],
]);

it('enforces panel access matrix correctly', function (string $role, string $panelId, bool $shouldHaveAccess) {
    $user = User::factory()->create(['role' => $role]);

    Filament::setCurrentPanel(Filament::getPanel($panelId));

    $url = '/'.Filament::getPanel($panelId)->getPath();

    $request = actingAs($user)->get($url);

    if ($shouldHaveAccess) {
        $request->assertOk();
    } else {
        $request->assertForbidden();
    }
})->with('panelAccessMatrix');

it('redirects guests from filament panels to central login', function (string $path) {
    get($path)->assertRedirect(route('login'));
})->with([
    '/cms',
    '/ops',
    '/data-steward',
]);

it('authenticates via central login and lands on dashboard hub for Sale', function () {
    $saleUser = User::factory()->create([
        'role' => 'Sale',
        'password' => bcrypt('test-password'),
    ]);

    post('/login', [
        'email' => $saleUser->email,
        'password' => 'test-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($saleUser);
});

it('authenticates Admin_PM via central login and lands on dashboard hub', function () {
    $admin = User::factory()->create([
        'role' => 'Admin_PM',
        'password' => bcrypt('test-password'),
    ]);

    post('/login', [
        'email' => $admin->email,
        'password' => 'test-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($admin);
});

it('authenticates DuLieuNen via central login and lands on dashboard hub', function () {
    $user = User::factory()->create([
        'role' => 'DuLieuNen',
        'password' => bcrypt('test-password'),
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'test-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('uses inertia location when logging in from an inertia request', function () {
    $saleUser = User::factory()->create([
        'role' => 'Sale',
        'password' => bcrypt('test-password'),
    ]);

    $this->withHeaders(['X-Inertia' => 'true'])
        ->post('/login', [
            'email' => $saleUser->email,
            'password' => 'test-password',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', URL::to('/dashboard'));

    $this->assertAuthenticatedAs($saleUser);
});

it('honors dashboard intended url for DuLieuNen', function () {
    $user = User::factory()->create([
        'role' => 'DuLieuNen',
        'password' => bcrypt('test-password'),
    ]);

    $this->withSession(['url.intended' => url('/dashboard')])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'test-password',
        ])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('authenticates Founder via central login and lands on dashboard hub', function () {
    $user = User::factory()->create([
        'role' => 'Founder',
        'password' => bcrypt('test-password'),
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'test-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('fails login with invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
