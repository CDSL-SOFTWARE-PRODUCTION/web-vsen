<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\OrderResource\Pages;


use App\Filament\Ops\Clusters\Demand\Resources\OrderResource;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{

    protected static string $resource = OrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if (
            (! isset($data['legal_entity_id']) || ! is_numeric($data['legal_entity_id']))
            && $user !== null
            && $user->legal_entity_id !== null
        ) {
            $data['legal_entity_id'] = $user->legal_entity_id;
        }

        $hasOrderCode = isset($data['order_code']) && trim((string) $data['order_code']) !== '';
        if (! $hasOrderCode && isset($data['tender_snapshot_id']) && is_numeric($data['tender_snapshot_id'])) {
            $tbmt = TenderSnapshot::query()->whereKey((int) $data['tender_snapshot_id'])->value('source_notify_no');
            $data['order_code'] = Order::buildOrderCodeFromTbmt($tbmt);
        }

        return $data;
    }
}
