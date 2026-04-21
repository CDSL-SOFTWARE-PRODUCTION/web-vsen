<?php

use App\Filament\Ops\Resources\System\FounderWorkCardResource;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('ops'));
});

it('allows admin pm to list founder work cards', function (): void {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    $this->actingAs($admin)
        ->get(FounderWorkCardResource::getUrl('index'))
        ->assertOk();
});

it('forbids non admin from founder work cards', function (): void {
    $sale = User::factory()->create(['role' => 'Sale']);

    $this->actingAs($sale)
        ->get(FounderWorkCardResource::getUrl('index'))
        ->assertForbidden();
});
