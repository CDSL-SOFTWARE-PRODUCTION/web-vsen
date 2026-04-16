<?php

namespace App\Domain\Demand;

use App\Domain\Audit\AuditLogService;
use App\Models\Demand\Order;
use App\Models\Ops\Contract;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderTransitionService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly OrderContractProjectionUpdater $projectionUpdater
    ) {
    }

    public function transition(Order $order, string $command, ?int $actorUserId = null): OrderTransitionResult
    {
        // #region agent log
        @file_get_contents('http://127.0.0.1:7271/ingest/c3f87a09-8801-4c97-9286-e3072a8d15fd', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Debug-Session-Id: dd6099\r\n",
                'content' => json_encode(['sessionId' => 'dd6099', 'runId' => 'phaseAtoC', 'hypothesisId' => 'H3', 'location' => 'OrderTransitionService.php:transition:entry', 'message' => 'Order transition requested', 'data' => ['order_id' => $order->id, 'from_state' => $order->state, 'command' => $command], 'timestamp' => round(microtime(true) * 1000)]),
                'timeout' => 1,
            ],
        ]));
        // #endregion
        $order->loadMissing(['items.priceListItem', 'contracts.documents', 'contracts.issues']);
        $fromState = $order->state;
        $toState = $this->resolveNextState($command, $fromState);
        $contract = $order->contracts()->first();
        if (! $contract instanceof Contract) {
            throw new RuntimeException('Order transition requires runtime contract projection.');
        }

        $warnings = $this->buildWarnings($command, $contract, $order);
        $result = new OrderTransitionResult(
            orderId: $order->id,
            command: $command,
            fromState: $fromState,
            toState: $toState,
            warningRaised: count($warnings) > 0,
            warnings: $warnings
        );

        DB::transaction(function () use ($order, $toState, $actorUserId, $command, $contract, $result): void {
            $order->transitionTo($toState);
            $this->projectionUpdater->syncFromOrder($order->fresh(), $contract->fresh());

            $this->auditLogService->log(
                actorUserId: $actorUserId,
                entityType: 'Order',
                entityId: $order->id,
                action: $command . 'Command',
                context: $result->toArray()
            );
        });

        return $result;
    }

    private function resolveNextState(string $command, string $fromState): string
    {
        $transitions = [
            'SubmitTender' => ['SubmitTender' => 'AwardTender'],
            'AwardTender' => ['SubmitTender' => 'AwardTender'],
            'ConfirmContract' => ['AwardTender' => 'ConfirmContract', 'SubmitTender' => 'ConfirmContract'],
            'StartExecution' => ['ConfirmContract' => 'StartExecution'],
            'ConfirmFulfillment' => ['StartExecution' => 'Fulfilled'],
            'CloseContract' => ['Fulfilled' => 'ContractClosed'],
            'AbandonTender' => ['SubmitTender' => 'Abandoned'],
        ];

        $nextState = $transitions[$command][$fromState] ?? null;
        if ($nextState === null) {
            throw new RuntimeException("Invalid order transition for command [{$command}] from state [{$fromState}].");
        }

        return $nextState;
    }

    /**
     * @return list<string>
     */
    private function buildWarnings(string $command, Contract $contract, Order $order): array
    {
        $warnings = [];
        if ($command === 'ConfirmContract' && $contract->tender_snapshot_id === null) {
            throw new RuntimeException('Cannot confirm contract: contract is not linked to tender snapshot proof.');
        }

        if (in_array($command, ['ConfirmContract', 'StartExecution', 'ConfirmFulfillment', 'CloseContract'], true)) {
            $missingDocs = $contract->documents()->where('status', 'missing')->count();
            if ($missingDocs > 0) {
                $warnings[] = "Missing documents: {$missingDocs}.";
            }
        }

        if ($command === 'ConfirmContract') {
            $thresholdPercent = 10.0;
            foreach ($order->items as $item) {
                if ($item->priceListItem === null || $item->unit_price === null) {
                    continue;
                }

                $priceListValue = (float) $item->priceListItem->unit_price;
                if ($priceListValue <= 0) {
                    continue;
                }

                $actualValue = (float) $item->unit_price;
                $deviationPercent = abs($actualValue - $priceListValue) / $priceListValue * 100;
                if ($deviationPercent > $thresholdPercent) {
                    $warnings[] = "Price deviation line {$item->line_no}: {$deviationPercent}% over allowed threshold.";
                }
            }
        }

        return $warnings;
    }
}
