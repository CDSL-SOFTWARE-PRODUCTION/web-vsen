<?php

namespace App\Domain\Finance;

use App\Contracts\Finance\MisaInvoicePort;
use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\FulfillmentReadiness;
use App\Domain\Execution\GateEvaluator;
use App\Events\InvoiceIssued;
use App\Models\Ops\Contract;
use App\Models\Ops\FinancialLedgerEntry;
use App\Models\Ops\Invoice;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IssueInvoiceService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly GateEvaluator $gateEvaluator,
        private readonly MisaInvoicePort $misaInvoicePort
    ) {}

    public function handle(int $contractId, float $totalAmount, ?int $actorUserId = null): Invoice
    {
        $contract = Contract::query()
            ->with(['deliveries', 'documents', 'order'])
            ->findOrFail($contractId);

        if ($contract->order_id === null) {
            throw new RuntimeException('Cannot issue invoice: contract has no order.');
        }

        $this->assertIssuePreconditions($contract);
        $this->assertPaymentMilestoneGate($contract, $actorUserId);

        $invoice = DB::transaction(function () use ($contract, $totalAmount, $actorUserId): Invoice {
            $order = $contract->order;
            $invoice = Invoice::query()->create([
                'legal_entity_id' => $order !== null ? $order->legal_entity_id : null,
                'order_id' => $contract->order_id,
                'contract_id' => $contract->id,
                'invoice_code' => 'INV-'.$contract->id.'-'.now()->format('YmdHis'),
                'total_amount' => $totalAmount,
                'status' => 'Issued',
                'payment_due_date' => now()->addDays(30)->toDateString(),
                'days_overdue_cached' => 0,
            ]);

            FinancialLedgerEntry::query()->create([
                'contract_id' => $contract->id,
                'invoice_id' => $invoice->id,
                'type' => 'Inflow',
                'amount' => $totalAmount,
                'memo' => 'Invoice '.$invoice->invoice_code,
            ]);

            $this->auditLogService->log(
                $actorUserId,
                'Invoice',
                $invoice->id,
                'IssueInvoice',
                ['contract_id' => $contract->id, 'invoice_code' => $invoice->invoice_code]
            );

            return $invoice;
        });

        $misaId = $this->misaInvoicePort->pushIssued($invoice);
        if ($misaId !== null) {
            $invoice->update(['misa_transaction_id' => $misaId]);
        }

        $issued = $invoice->fresh();
        if ($issued === null) {
            throw new RuntimeException('Invoice issue failed after persist.');
        }
        InvoiceIssued::dispatch($issued);

        return $issued;
    }

    private function assertIssuePreconditions(Contract $contract): void
    {
        if (! FulfillmentReadiness::hasDeliveredShipment($contract)) {
            throw new RuntimeException('Cannot issue invoice: no delivery in Delivered status.');
        }
        if (! FulfillmentReadiness::hasAcceptanceMinuteNotMissing($contract)) {
            throw new RuntimeException('Cannot issue invoice: Acceptance Minute document still missing.');
        }
    }

    private function assertPaymentMilestoneGate(Contract $contract, ?int $actorUserId): void
    {
        $contract->loadMissing('paymentMilestones');
        $evaluation = $this->gateEvaluator->evaluatePrePayment($contract);
        $mode = config('ops.gates.invoice_payment_milestone', 'warn');
        if (! $evaluation['hasWarnings']) {
            return;
        }
        if ($mode === 'hard') {
            throw new RuntimeException(
                'Cannot issue invoice: payment milestone checklist incomplete. '
                .implode(' ', $evaluation['warnings'])
            );
        }

        $this->auditLogService->log(
            $actorUserId,
            'Contract',
            $contract->id,
            'IssueInvoicePaymentMilestoneWarn',
            ['warnings' => $evaluation['warnings']]
        );
    }
}
