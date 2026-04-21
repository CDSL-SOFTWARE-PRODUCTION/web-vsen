<?php

use App\Filament\Ops\Pages\FounderInbox;
use App\Models\Ops\FounderWorkCard;
use App\Models\User;

it('redirects founder away from deep ops urls to the inbox', function (): void {
    $founder = User::factory()->create([
        'role' => 'Founder',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($founder);

    $this->get('/ops/demand/contracts')
        ->assertRedirect(FounderInbox::getUrl(panel: 'ops'));
});

it('lets founder open the inbox and digest export', function (): void {
    $founder = User::factory()->create([
        'role' => 'Founder',
        'email_verified_at' => now(),
    ]);

    FounderWorkCard::factory()->create([
        'founder_user_id' => $founder->id,
        'digest_lane' => FounderWorkCard::LANE_SIGNATURE,
        'due_at' => now()->subDay(),
    ]);

    $this->actingAs($founder);

    $this->get(FounderInbox::getUrl(panel: 'ops'))
        ->assertOk()
        ->assertSee(__('ops.founder_inbox.title'), false);

    $this->get(route('ops.founder.digest-export'))
        ->assertOk()
        ->assertSee(__('ops.founder_inbox.digest_export.heading'), false);
});

it('does not redirect non-founder ops users', function (): void {
    $sale = User::factory()->create([
        'role' => 'Sale',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($sale);

    $this->get('/ops/demand-workspace')->assertOk();
});
