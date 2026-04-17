<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use App\Models\Ops\Contract;
use App\Models\Ops\Document;
use App\Models\Ops\Partner;
use RuntimeException;

/**
 * Maps model/constraints.yaml C-ORD-* checks to runtime commands.
 * Gate modes: warn (default, preserves tests) | hard | off — see config/ops.php.
 */
final class OrderConstraintChecks
{
    private const AWARD_DOC_TYPES = ['DOC_HSMT', 'DOC_HSDT', 'BL_DU_THAU'];

    private const CONFIRM_HD_KY = 'HD_KY';

    private const CLOSE_DOC_TYPES = ['DOC_BB_THANH_LY', 'DOC_CV_HOAN_TRA_BL'];

    /**
     * Hard stops before transition (throw) or nothing when mode is warn/off.
     *
     * @return list<string> Extra warnings when mode is warn
     */
    public function assertAndCollectWarnings(
        Order $order,
        Contract $contract,
        string $command,
        string $fromState
    ): array {
        $warnings = [];

        if ($command === 'AwardTender' && $fromState === 'SubmitTender') {
            $warnings = array_merge($warnings, $this->checkAwardTenderDocs($contract));
        }

        if ($command === 'ConfirmContract') {
            $warnings = array_merge($warnings, $this->checkConfirmHdKy($contract));
            $warnings = array_merge($warnings, $this->checkConfirmCertWarnings($order));
            $warnings = array_merge($warnings, $this->checkConfirmProfitRisk($order));
        }

        if ($command === 'CloseContract') {
            $warnings = array_merge($warnings, $this->checkCloseContractDocs($contract));
        }

        return $warnings;
    }

    public function assertConfirmContractCreditLimit(Order $order, Contract $contract): void
    {
        if ($contract->customer_partner_id === null) {
            return;
        }

        $mode = (string) config('ops.gates.confirm_contract_credit_limit', 'warn');
        if ($mode === 'off') {
            return;
        }

        $partner = Partner::query()->find($contract->customer_partner_id);
        if ($partner === null || $partner->credit_limit === null) {
            return;
        }

        $total = (float) $order->items->sum(function ($item): float {
            $q = (float) $item->quantity;
            $p = (float) ($item->unit_price ?? 0);

            return $q * $p;
        });

        $limit = (float) $partner->credit_limit;
        if ($total <= $limit) {
            return;
        }

        $msg = 'C-ORD-003: Order total '.round($total, 2).' exceeds customer credit limit '.round($limit, 2).'.';
        if ($mode === 'hard') {
            throw new RuntimeException($msg);
        }
    }

    /**
     * @return list<string>
     */
    private function checkAwardTenderDocs(Contract $contract): array
    {
        $mode = (string) config('ops.gates.award_tender_required_docs', 'warn');
        if ($mode === 'off') {
            return [];
        }

        $missing = [];
        foreach (self::AWARD_DOC_TYPES as $type) {
            if (! $this->hasDocumentUploaded($contract, $type)) {
                $missing[] = $type;
            }
        }

        if ($missing === []) {
            return [];
        }

        $msg = 'C-ORD-001: Missing tender documents (must be uploaded): '.implode(', ', $missing).'.';

        return $this->applyDocGate($mode, $msg);
    }

    /**
     * @return list<string>
     */
    private function checkConfirmHdKy(Contract $contract): array
    {
        $mode = (string) config('ops.gates.confirm_contract_hd_ky', 'warn');
        if ($mode === 'off') {
            return [];
        }

        if ($this->hasDocumentUploaded($contract, self::CONFIRM_HD_KY)) {
            return [];
        }

        $msg = 'C-ORD-003: Signed contract (HD_KY) not uploaded.';

        return $this->applyDocGate($mode, $msg);
    }

    /**
     * @return list<string>
     */
    private function checkCloseContractDocs(Contract $contract): array
    {
        $mode = (string) config('ops.gates.close_contract_required_docs', 'warn');
        if ($mode === 'off') {
            return [];
        }

        $missing = [];
        foreach (self::CLOSE_DOC_TYPES as $type) {
            if (! $this->hasDocumentUploaded($contract, $type)) {
                $missing[] = $type;
            }
        }

        if ($missing === []) {
            return [];
        }

        $msg = 'C-ORD-004: Missing closing documents: '.implode(', ', $missing).'.';

        return $this->applyDocGate($mode, $msg);
    }

    /**
     * C-ORD-002: warn when CO-CQ still missing (proxy for cert cross-check).
     *
     * @return list<string>
     */
    private function checkConfirmCertWarnings(Order $order): array
    {
        $mode = (string) config('ops.gates.confirm_contract_cert_crosscheck', 'warn');
        if ($mode === 'off') {
            return [];
        }

        $contract = $order->contracts()->first();
        if (! $contract instanceof Contract) {
            return [];
        }

        $cq = Document::query()
            ->where('contract_id', $contract->id)
            ->where('document_type', 'CO-CQ')
            ->first();

        if ($cq !== null && $cq->status !== 'missing') {
            return [];
        }

        return ['C-ORD-002: CO-CQ certificate document still missing (cross-check ISO/FSC vs tender requirements).'];
    }

    /**
     * C-ORD-006: warn when line economics look loss-making vs price list.
     *
     * @return list<string>
     */
    private function checkConfirmProfitRisk(Order $order): array
    {
        $mode = (string) config('ops.gates.confirm_contract_negative_margin', 'warn');
        if ($mode === 'off') {
            return [];
        }

        $warnings = [];
        foreach ($order->items as $item) {
            if ($item->priceListItem === null || $item->unit_price === null) {
                continue;
            }
            $list = (float) $item->priceListItem->unit_price;
            $actual = (float) $item->unit_price;
            if ($list > 0 && $actual < $list) {
                $warnings[] = 'C-ORD-006: Line '.$item->line_no.' unit price below list (estimated margin risk).';
            }
        }

        return $warnings;
    }

    private function hasDocumentUploaded(Contract $contract, string $documentType): bool
    {
        return Document::query()
            ->where('contract_id', $contract->id)
            ->where('document_type', $documentType)
            ->where('status', '!=', 'missing')
            ->exists();
    }

    /**
     * @return list<string>
     */
    private function applyDocGate(string $mode, string $message): array
    {
        if ($mode === 'hard') {
            throw new RuntimeException($message);
        }

        if ($mode === 'warn') {
            return [$message];
        }

        return [];
    }
}
