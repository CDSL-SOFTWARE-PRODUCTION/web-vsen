<?php

namespace Database\Seeders;

use App\Models\Ops\CashPlanEvent;
use App\Models\Ops\Contract;
use App\Models\Ops\ContractItem;
use App\Models\Ops\Document;
use App\Models\Ops\ExecutionIssue;
use App\Models\Ops\Partner;
use App\Models\Ops\PaymentMilestone;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpsV1Seeder extends Seeder
{
    public function run(): void
    {
        Contract::query()->delete();
        Partner::query()->delete();

        $owner = User::query()->first();

        $vendorAlpha = Partner::query()->create([
            'name' => 'Vendor Alpha Med',
            'type' => 'Supplier',
            'segment' => 'Hospital',
            'lead_time_days' => 14,
            'reliability_note' => 'High delay rate in Q1',
        ]);

        $vendorBravo = Partner::query()->create([
            'name' => 'Vendor Bravo Devices',
            'type' => 'Supplier',
            'segment' => 'Hospital',
            'lead_time_days' => 7,
            'reliability_note' => 'Stable quality',
        ]);

        $contract = Contract::query()->create([
            'contract_code' => 'CT-BV-2026-001',
            'name' => 'Goi thau monitor ICU 2026',
            'customer_name' => 'Benh vien Trung Uong A',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'allocated_budget' => 900000000,
            'next_delivery_due_date' => now()->addDays(12)->toDateString(),
            'risk_level' => 'Amber',
            'open_items_count' => 3,
            'open_issues_count' => 2,
            'missing_docs_count' => 2,
            'cash_needed_14d' => 420000000,
        ]);

        $item1 = ContractItem::query()->create([
            'contract_id' => $contract->id,
            'partner_id' => $vendorAlpha->id,
            'item_code' => 'ICU-MON-01',
            'name' => 'ICU Monitor 12 inch',
            'spec' => '12 inch, 6 parameter',
            'quantity' => 20,
            'delivery_deadline' => now()->addDays(8)->toDateString(),
            'lead_time_days' => 14,
            'status' => 'vendor_confirmed',
            'docs_status' => 'missing',
            'cash_status' => 'need_fund',
            'is_critical' => true,
            'line_risk_level' => 'Red',
        ]);

        $item2 = ContractItem::query()->create([
            'contract_id' => $contract->id,
            'partner_id' => $vendorBravo->id,
            'item_code' => 'ICU-MON-02',
            'name' => 'ICU Monitor transport',
            'spec' => 'Portable, battery 6h',
            'quantity' => 10,
            'delivery_deadline' => now()->addDays(20)->toDateString(),
            'lead_time_days' => 7,
            'status' => 'inbound',
            'docs_status' => 'complete',
            'cash_status' => 'not_needed',
            'is_critical' => false,
            'line_risk_level' => 'Green',
        ]);

        $item3 = ContractItem::query()->create([
            'contract_id' => $contract->id,
            'partner_id' => $vendorAlpha->id,
            'item_code' => 'ICU-MON-03',
            'name' => 'Monitor cable kit',
            'spec' => 'Cable set',
            'quantity' => 30,
            'delivery_deadline' => now()->addDays(11)->toDateString(),
            'lead_time_days' => 10,
            'status' => 'not_ordered',
            'docs_status' => 'missing',
            'cash_status' => 'need_fund',
            'is_critical' => true,
            'line_risk_level' => 'Amber',
        ]);

        ExecutionIssue::query()->create([
            'contract_id' => $contract->id,
            'contract_item_id' => $item1->id,
            'owner_user_id' => $owner?->id,
            'issue_type' => 'Delay',
            'severity' => 'High',
            'impact_flags' => ['deadline', 'cost'],
            'due_at' => now()->addDays(2),
            'status' => 'InProgress',
            'description' => 'Vendor thong bao doi lich xuat xuong',
        ]);

        ExecutionIssue::query()->create([
            'contract_id' => $contract->id,
            'contract_item_id' => $item1->id,
            'owner_user_id' => $owner?->id,
            'issue_type' => 'DocMissing',
            'severity' => 'Medium',
            'impact_flags' => ['documents'],
            'due_at' => now()->addDays(3),
            'status' => 'Open',
            'description' => 'Thieu CQ ban scan',
        ]);

        $milestone1 = PaymentMilestone::query()->create([
            'contract_id' => $contract->id,
            'name' => 'Tam ung 30%',
            'due_date' => now()->addDays(7)->toDateString(),
            'amount_planned' => 270000000,
            'checklist_status' => 'partial',
            'payment_ready' => false,
        ]);

        PaymentMilestone::query()->create([
            'contract_id' => $contract->id,
            'name' => 'Nghiem thu dot 1',
            'due_date' => now()->addDays(28)->toDateString(),
            'amount_planned' => 360000000,
            'checklist_status' => 'pending',
            'payment_ready' => false,
        ]);

        Document::query()->create([
            'contract_id' => $contract->id,
            'contract_item_id' => $item1->id,
            'document_group' => 'quality_legal',
            'document_type' => 'CO',
            'status' => 'uploaded',
            'file_path' => 'ops/sample/co_item1.pdf',
        ]);

        Document::query()->create([
            'contract_id' => $contract->id,
            'contract_item_id' => $item1->id,
            'document_group' => 'quality_legal',
            'document_type' => 'CQ',
            'status' => 'missing',
        ]);

        Document::query()->create([
            'contract_id' => $contract->id,
            'payment_milestone_id' => $milestone1->id,
            'document_group' => 'acceptance_payment',
            'document_type' => 'Bien ban nghiem thu',
            'status' => 'missing',
        ]);

        CashPlanEvent::query()->create([
            'contract_id' => $contract->id,
            'partner_id' => $vendorAlpha->id,
            'scheduled_date' => now()->addDays(6)->toDateString(),
            'amount' => 220000000,
            'purpose' => 'PaySupplier',
        ]);

        CashPlanEvent::query()->create([
            'contract_id' => $contract->id,
            'partner_id' => $vendorBravo->id,
            'scheduled_date' => now()->addDays(13)->toDateString(),
            'amount' => 200000000,
            'purpose' => 'PaySupplier',
        ]);
    }
}
