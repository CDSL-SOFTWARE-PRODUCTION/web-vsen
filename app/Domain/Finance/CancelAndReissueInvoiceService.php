<?php

namespace App\Domain\Finance;

use App\Domain\Audit\AuditLogService;
use App\Models\Ops\Invoice;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * C-FIN-002: void issued invoice and create a replacement; link via replaced_by_invoice_id on the voided row.
 */
class CancelAndReissueInvoiceService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function handle(int $invoiceId, float $newTotalAmount, ?int $actorUserId = null): Invoice
    {
        $original = Invoice::query()->withoutGlobalScopes()->findOrFail($invoiceId);

        if ($original->status !== 'Issued') {
            throw new RuntimeException('Only Issued invoices can be cancelled and reissued.');
        }

        return DB::transaction(function () use ($original, $newTotalAmount, $actorUserId): Invoice {
            $replacement = Invoice::query()->withoutGlobalScopes()->create([
                'legal_entity_id' => $original->legal_entity_id,
                'order_id' => $original->order_id,
                'contract_id' => $original->contract_id,
                'invoice_code' => 'INV-R-'.$original->contract_id.'-'.now()->format('YmdHis'),
                'total_amount' => $newTotalAmount,
                'status' => 'Issued',
                'payment_due_date' => now()->addDays(30)->toDateString(),
                'days_overdue_cached' => 0,
            ]);

            $original->update([
                'status' => 'Voided',
                'replaced_by_invoice_id' => $replacement->id,
            ]);

            $this->auditLogService->log(
                $actorUserId,
                'Invoice',
                $original->id,
                'CancelAndReissue',
                [
                    'replacement_invoice_id' => $replacement->id,
                    'replacement_invoice_code' => $replacement->invoice_code,
                ]
            );

            return $replacement->fresh();
        });
    }
}
