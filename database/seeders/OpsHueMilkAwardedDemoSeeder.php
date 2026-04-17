<?php

namespace Database\Seeders;

use App\Domain\Demand\CloseContractCommandService;
use App\Domain\Demand\ConfirmContractCommandService;
use App\Domain\Demand\ConfirmFulfillmentCommandService;
use App\Domain\Demand\StartExecutionCommandService;
use App\Domain\Execution\GenerateExecutionPlanService;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use App\Models\Ops\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Demo: TBMT IB2600147617 (sữa — ĐH Y Dược Huế) — snapshot khóa + Order/Contract như sau trúng thầu.
 *
 * Chạy: php artisan db:seed --class=OpsHueMilkAwardedDemoSeeder
 *
 * Mặc định dừng ở AwardTender (để bạn bấm transition trên Filament).
 * Đóng full lifecycle đến ContractClosed: OPS_SEED_HUE_MILK_FULL=true (seed luôn giao hàng + biên bản + các bước command).
 */
class OpsHueMilkAwardedDemoSeeder extends Seeder
{
    private const FIXTURE_RELATIVE = 'fixtures/tender_snapshots/ib2600147617-milk-hue-2025.json';

    public function run(): void
    {
        $path = database_path(self::FIXTURE_RELATIVE);
        if (! File::exists($path)) {
            throw new RuntimeException('Missing fixture: '.$path);
        }

        /** @var array{meta: array<string, mixed>, items: list<array<string, mixed>>} $data */
        $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $notifyNo = (string) $data['meta']['source_notify_no'];

        if (TenderSnapshot::query()
            ->where('source_system', 'muasamcong')
            ->where('source_notify_no', $notifyNo)
            ->exists()) {
            $this->command?->warn('Skipping: TBMT '.$notifyNo.' already exists (migrate:fresh to reseed).');

            return;
        }

        $actor = User::query()->where('role', 'Admin_PM')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if ($actor === null) {
            throw new RuntimeException('No user found; run DatabaseSeeder first.');
        }

        $snapshot = TenderSnapshot::query()->create([
            'source_system' => (string) $data['meta']['source_system'],
            'source_notify_no' => $notifyNo,
            'source_plan_no' => (string) $data['meta']['source_plan_no'],
        ]);

        foreach ($data['items'] as $row) {
            TenderSnapshotItem::query()->create([
                'tender_snapshot_id' => $snapshot->id,
                'line_no' => (int) $row['line_no'],
                'name' => (string) $row['name'],
                'uom' => (string) $row['uom'],
                'quantity_awarded' => (float) $row['quantity_awarded'],
                'tender_item_ref' => isset($row['tender_item_ref']) ? (string) $row['tender_item_ref'] : null,
                'brand' => isset($row['brand']) ? (string) $row['brand'] : null,
                'manufacturer' => isset($row['manufacturer']) ? (string) $row['manufacturer'] : null,
                'origin_country' => isset($row['origin_country']) ? (string) $row['origin_country'] : null,
                'manufacture_year' => isset($row['manufacture_year']) ? (int) $row['manufacture_year'] : null,
                'spec_committed_raw' => isset($row['spec_committed_raw']) ? (string) $row['spec_committed_raw'] : null,
                'project_site' => isset($row['project_site']) ? (string) $row['project_site'] : null,
                'delivery_earliest_rule' => isset($row['delivery_earliest_rule']) ? (string) $row['delivery_earliest_rule'] : null,
                'delivery_latest_rule' => isset($row['delivery_latest_rule']) ? (string) $row['delivery_latest_rule'] : null,
                'other_requirements_raw' => isset($row['other_requirements_raw']) ? (string) $row['other_requirements_raw'] : null,
            ]);
        }

        $snapshot->refresh();
        $snapshot->lock($actor->id);

        $contract = app(GenerateExecutionPlanService::class)->handle($snapshot->id, $actor->id);

        $ceiling = $data['meta']['ceiling_price_vnd'] ?? null;
        Contract::query()->whereKey($contract->id)->update([
            'name' => (string) $data['meta']['title'],
            'customer_name' => (string) $data['meta']['investor'],
            'allocated_budget' => $ceiling !== null ? (float) $ceiling : $contract->allocated_budget,
        ]);

        /** @var Order $order */
        $order = Order::query()->findOrFail($contract->order_id);

        $this->command?->info(sprintf(
            'Hue milk demo: Snapshot #%d | Contract #%d | Order #%s (%s)',
            $snapshot->id,
            $contract->id,
            $order->id,
            $order->order_code
        ));

        if (! filter_var(env('OPS_SEED_HUE_MILK_FULL', false), FILTER_VALIDATE_BOOL)) {
            $this->command?->info('Set OPS_SEED_HUE_MILK_FULL=true to seed delivery + run transitions to ContractClosed.');

            return;
        }

        $this->seedFulfillmentArtifacts($contract, $order);
        $this->markContractDocumentsUploaded($contract->id);

        app(ConfirmContractCommandService::class)->handle($order->id, $actor->id);
        $order->refresh();

        app(StartExecutionCommandService::class)->handle($order->id, $actor->id);
        $order->refresh();

        app(ConfirmFulfillmentCommandService::class)->handle($order->id, $actor->id);
        $order->refresh();

        app(CloseContractCommandService::class)->handle($order->id, $actor->id);
        $order->refresh();

        $this->command?->info('Order state: '.$order->state.' (expected ContractClosed).');
    }

    private function seedFulfillmentArtifacts(Contract $contract, Order $order): void
    {
        Delivery::query()->create([
            'order_id' => $order->id,
            'contract_id' => $contract->id,
            'source_warehouse_code' => 'WH-DEMO',
            'tracking_code' => 'DLV-HUE-MILK-'.now()->format('Ymd'),
            'status' => 'Delivered',
            'dispatched_at' => now()->subDay(),
            'delivered_at' => now()->subHours(2),
        ]);
    }

    private function markContractDocumentsUploaded(int $contractId): void
    {
        Document::query()
            ->where('contract_id', $contractId)
            ->update([
                'status' => 'uploaded',
                'file_path' => 'ops/demo/'.$contractId.'/placeholder.pdf',
            ]);
    }
}
