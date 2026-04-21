<?php

use App\Models\User;

it('refuses to demote admin pm without force', function (): void {
    $user = User::factory()->create([
        'role' => 'Admin_PM',
        'email' => 'admin-pm-role-test@example.test',
    ]);

    $this->artisan('ops:set-founder-role', ['email' => $user->email])
        ->assertFailed();

    expect(User::query()->find($user->id)?->role)->toBe('Admin_PM');
});

it('dry run does not persist', function (): void {
    $user = User::factory()->create([
        'role' => 'Sale',
        'email' => 'sale-founder-dry@example.test',
    ]);

    $this->artisan('ops:set-founder-role', ['email' => $user->email, '--dry-run' => true])
        ->assertSuccessful();

    expect(User::query()->find($user->id)?->role)->toBe('Sale');
});

it('assigns founder with force from admin pm', function (): void {
    $user = User::factory()->create([
        'role' => 'Admin_PM',
        'email' => 'admin-pm-to-founder@example.test',
    ]);

    $this->artisan('ops:set-founder-role', ['email' => $user->email, '--force' => true])
        ->assertSuccessful();

    expect(User::query()->find($user->id)?->role)->toBe('Founder');
});
