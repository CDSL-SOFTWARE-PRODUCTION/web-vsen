<?php

namespace App\Domain\Execution;

use App\Models\Ops\Contract;

class GateEvaluator
{
    public function evaluatePreActivate(Contract $contract): array
    {
        $warnings = [];

        if ($contract->tender_snapshot_id === null) {
            $warnings[] = 'Snapshot is not linked.';
        }

        if ($contract->items()->count() === 0) {
            $warnings[] = 'No line-items mapped to contract.';
        }

        if ($contract->documents()->count() === 0) {
            $warnings[] = 'Document checklist has not been seeded.';
        }

        return $this->result($warnings);
    }

    public function evaluatePreDelivery(Contract $contract): array
    {
        $warnings = [];

        $missingDocs = $contract->documents()
            ->where('status', 'missing')
            ->count();
        if ($missingDocs > 0) {
            $warnings[] = "Missing documents: {$missingDocs}.";
        }

        $blockingIssues = $contract->issues()
            ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
            ->whereIn('issue_type', ['DocMissing', 'Quality'])
            ->count();
        if ($blockingIssues > 0) {
            $warnings[] = "Open DocMissing/Quality issues: {$blockingIssues}.";
        }

        return $this->result($warnings);
    }

    public function evaluatePrePayment(Contract $contract): array
    {
        $warnings = [];

        $incompleteMilestones = $contract->paymentMilestones()
            ->where('checklist_status', '!=', 'complete')
            ->count();
        if ($incompleteMilestones > 0) {
            $warnings[] = "Milestones with incomplete checklist: {$incompleteMilestones}.";
        }

        return $this->result($warnings);
    }

    private function result(array $warnings): array
    {
        return [
            'hasWarnings' => count($warnings) > 0,
            'warnings' => $warnings,
        ];
    }
}

