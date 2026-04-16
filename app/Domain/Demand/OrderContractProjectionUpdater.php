<?php

namespace App\Domain\Demand;

use App\Models\Demand\Order;
use App\Models\Ops\Contract;

class OrderContractProjectionUpdater
{
    public function syncFromOrder(Order $order, Contract $contract): void
    {
        $openIssuesCount = $contract->issues()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->count();
        $missingDocsCount = $contract->documents()
            ->where('status', 'missing')
            ->count();

        $riskLevel = $this->resolveRiskLevel($order->state, $openIssuesCount, $missingDocsCount);

        $contract->update([
            'risk_level' => $riskLevel,
            'open_issues_count' => $openIssuesCount,
            'missing_docs_count' => $missingDocsCount,
        ]);
    }

    private function resolveRiskLevel(string $orderState, int $openIssuesCount, int $missingDocsCount): string
    {
        if ($openIssuesCount > 0 || $missingDocsCount > 0) {
            return 'Amber';
        }

        if (in_array($orderState, ['Fulfilled', 'ContractClosed'], true)) {
            return 'Green';
        }

        return 'Green';
    }
}
