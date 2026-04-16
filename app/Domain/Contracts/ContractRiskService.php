<?php

namespace App\Domain\Contracts;

use App\Models\Ops\Contract;
use App\Models\Ops\ContractItem;

class ContractRiskService
{
    /**
     * Recompute contract and line-item risk cache.
     */
    public function recomputeAll(): void
    {
        $contracts = Contract::query()
            ->with(['items.issues', 'cashPlanEvents', 'issues', 'documents'])
            ->get();

        foreach ($contracts as $contract) {
            $risk = 'Green';

            $openItemsCount = $contract->items
                ->whereNotIn('status', ['delivered', 'accepted'])
                ->count();

            $openIssuesCount = $contract->issues
                ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
                ->count();

            $missingDocsCount = $contract->documents
                ->where('status', 'missing')
                ->count();

            $cashNeeded14d = $contract->cashPlanEvents
                ->filter(fn ($event): bool => $event->scheduled_date !== null
                    && $event->scheduled_date->between(now()->startOfDay(), now()->copy()->addDays(14)->endOfDay()))
                ->sum('amount');

            foreach ($contract->items as $item) {
                $lineRisk = $this->computeItemRisk($item);
                if ($lineRisk === 'Red') {
                    $risk = 'Red';
                } elseif ($lineRisk === 'Amber' && $risk !== 'Red') {
                    $risk = 'Amber';
                }
                $item->update(['line_risk_level' => $lineRisk]);
            }

            if ($openIssuesCount > 0 && $risk === 'Green') {
                $risk = 'Amber';
            }

            if ($cashNeeded14d > (float) $contract->allocated_budget) {
                $risk = 'Red';
            }

            $contract->update([
                'risk_level' => $risk,
                'open_items_count' => $openItemsCount,
                'open_issues_count' => $openIssuesCount,
                'missing_docs_count' => $missingDocsCount,
                'cash_needed_14d' => $cashNeeded14d,
                'next_delivery_due_date' => $contract->items->min('delivery_deadline'),
            ]);
        }
    }

    private function computeItemRisk(ContractItem $item): string
    {
        if (in_array($item->status, ['delivered', 'accepted'], true)) {
            return 'Green';
        }

        $daysToDeadline = now()->startOfDay()->diffInDays($item->delivery_deadline, false);
        if ($daysToDeadline <= $item->lead_time_days) {
            return 'Red';
        }

        $hasBlockingIssue = $item->issues
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->whereIn('issue_type', ['DocMissing', 'Quality'])
            ->isNotEmpty();

        if ($item->docs_status === 'missing' || $hasBlockingIssue) {
            return 'Red';
        }

        if ($item->cash_status === 'need_fund') {
            return 'Amber';
        }

        return 'Green';
    }
}
