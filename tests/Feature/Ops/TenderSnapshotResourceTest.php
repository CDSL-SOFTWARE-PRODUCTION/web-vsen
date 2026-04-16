<?php

use App\Filament\Ops\Resources\TenderSnapshotResource;
use App\Models\Demand\TenderSnapshot;
use App\Models\Demand\TenderSnapshotAttachment;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('ops'));
});

it('can access tender snapshot list page', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);

    actingAs($admin)
        ->get(TenderSnapshotResource::getUrl())
        ->assertOk();
});

it('locks snapshot and sets hash fields', function () {
    $admin = User::factory()->create(['role' => 'Admin_PM']);
    actingAs($admin);

    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-001',
        'source_plan_no' => 'PLAN-001',
    ]);

    TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'May loc nuoc',
        'uom' => 'May',
        'quantity_awarded' => 29,
    ]);

    TenderSnapshotAttachment::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'label' => 'HSMT',
        'file_path' => 'files/hsmt.pdf',
    ]);

    $snapshot->lock($admin->id);
    $snapshot->refresh();

    expect($snapshot->locked_at)->not->toBeNull()
        ->and($snapshot->locked_by_user_id)->toBe($admin->id)
        ->and($snapshot->snapshot_hash)->not->toBeEmpty();
});

it('prevents snapshot update after lock', function () {
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-002',
    ]);

    $snapshot->lock();

    $snapshot->source_plan_no = 'PLAN-CHANGED';

    expect(fn () => $snapshot->save())->toThrow(RuntimeException::class);
});

it('prevents item mutation after lock', function () {
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-003',
    ]);

    $item = TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 1,
        'name' => 'Vat tu A',
        'uom' => 'Cai',
        'quantity_awarded' => 10,
    ]);

    $snapshot->lock();

    $item->name = 'Vat tu A updated';

    expect(fn () => $item->save())->toThrow(RuntimeException::class);
    expect(fn () => TenderSnapshotItem::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'line_no' => 2,
        'name' => 'Vat tu B',
        'uom' => 'Cai',
        'quantity_awarded' => 5,
    ]))->toThrow(RuntimeException::class);
});

it('prevents attachment mutation after lock', function () {
    $snapshot = TenderSnapshot::query()->create([
        'source_system' => 'muasamcong',
        'source_notify_no' => 'TBMT-004',
    ]);

    $attachment = TenderSnapshotAttachment::query()->create([
        'tender_snapshot_id' => $snapshot->id,
        'label' => 'Ban ve',
        'file_path' => 'files/banve.pdf',
    ]);

    $snapshot->lock();

    $attachment->label = 'Ban ve updated';

    expect(fn () => $attachment->save())->toThrow(RuntimeException::class);
});

