<?php

namespace App\Domain\Demand;

use App\Domain\Audit\AuditLogService;
use App\Domain\Supply\GenerateSupplyOrderFromOrderService;
use App\Models\Demand\BidOpeningLine;
use App\Models\Demand\BidOpeningSession;
use App\Models\Demand\Order;
use App\Models\Demand\OrderItem;
use App\Models\LegalEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateOrderFromBidOpeningSessionService
{
    public function __construct(
        private readonly BidOpeningMappingGateService $mappingGateService,
        private readonly AuditLogService $auditLogService,
        private readonly GenerateSupplyOrderFromOrderService $generateSupplyOrderFromOrderService
    ) {}

    /**
     * @return array{order_id:int,order_items_count:int}
     */
    public function handle(int $sessionId, ?int $actorUserId = null): array
    {
        $session = BidOpeningSession::query()
            ->with(['lines', 'awardOutcomes', 'tenderSnapshot.items'])
            ->findOrFail($sessionId);

        if ($session->lines->count() === 0) {
            throw new RuntimeException('Cannot create order: bid opening session has no lines.');
        }

        $this->mappingGateService->assertAllLinesMapped($session);

        $legalEntityId = $actorUserId !== null
            ? User::query()->whereKey($actorUserId)->value('legal_entity_id')
            : null;
        if ($legalEntityId === null) {
            $legalEntityId = LegalEntity::query()->orderBy('id')->value('id');
        }

        return DB::transaction(function () use ($session, $actorUserId, $legalEntityId): array {
            $order = new Order([
                'legal_entity_id' => $legalEntityId,
                'order_code' => Order::buildOrderCodeFromTbmt($session->source_notify_no),
                'name' => 'Order from BBMT '.$session->source_notify_no,
                'tender_snapshot_id' => $session->tender_snapshot_id,
                'awarded_at' => $session->opened_at ?? now(),
            ]);
            $order->setInitialState('AwardTender');
            $order->save();

            $linesByLot = $session->lines->groupBy(fn (BidOpeningLine $line): string => (string) $line->lot_code);
            $createdItems = 0;

            foreach ($linesByLot as $lotCode => $lotLines) {
                $selected = $this->selectLineForLot($session, $lotCode, $lotLines);
                if ($selected === null || $selected->canonical_product_id === null) {
                    throw new RuntimeException("Cannot create order item for lot {$lotCode}: missing canonical product mapping.");
                }

                $snapshotItem = $session->tenderSnapshot?->items
                    ?->firstWhere('tender_item_ref', $lotCode);
                $quantity = (float) ($snapshotItem?->quantity_awarded ?? 1);
                $uom = $snapshotItem?->uom;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'line_no' => $createdItems + 1,
                    'name' => $selected->item_name ?? $selected->lot_code,
                    'uom' => $uom,
                    'quantity' => $quantity,
                    'status' => 'planned',
                    'procurement_status' => 'pending',
                    'canonical_product_id' => $selected->canonical_product_id,
                    'unit_price' => $this->effectivePrice($selected),
                ]);
                $createdItems++;
            }

            $this->auditLogService->log(
                actorUserId: $actorUserId,
                entityType: 'BidOpeningSession',
                entityId: $session->id,
                action: 'CreateOrderFromBidOpeningSession',
                context: [
                    'order_id' => $order->id,
                    'order_items_count' => $createdItems,
                ]
            );

            $this->generateSupplyOrderFromOrderService->handle($order->id, $actorUserId);

            return [
                'order_id' => $order->id,
                'order_items_count' => $createdItems,
            ];
        });
    }

    /**
     * @param  Collection<int, BidOpeningLine>  $lotLines
     */
    private function selectLineForLot(BidOpeningSession $session, string $lotCode, Collection $lotLines): ?BidOpeningLine
    {
        $award = $session->awardOutcomes->firstWhere('lot_code', $lotCode);
        if ($award !== null) {
            $winner = $lotLines->first(function (BidOpeningLine $line) use ($award): bool {
                $sameIdentifier = $award->winning_bidder_identifier !== null
                    && $award->winning_bidder_identifier !== ''
                    && $line->bidder_identifier === $award->winning_bidder_identifier;
                $sameName = $line->bidder_name === $award->winning_bidder_name;

                return $sameIdentifier || $sameName;
            });
            if ($winner instanceof BidOpeningLine) {
                return $winner;
            }
        }

        /** @var BidOpeningLine|null $fallback */
        $fallback = $lotLines
            ->sortBy(fn (BidOpeningLine $line): float => $this->effectivePrice($line))
            ->first();

        return $fallback;
    }

    private function effectivePrice(BidOpeningLine $line): float
    {
        if ($line->bid_price_after_discount !== null) {
            return (float) $line->bid_price_after_discount;
        }

        return (float) $line->bid_price;
    }
}
