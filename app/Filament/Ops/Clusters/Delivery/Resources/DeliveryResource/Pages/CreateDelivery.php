<?php

namespace App\Filament\Ops\Clusters\Delivery\Resources\DeliveryResource\Pages;

use App\Filament\Ops\Clusters\Delivery\Resources\DeliveryResource;
use App\Models\Ops\Contract;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['contract_id'])) {
            $contract = Contract::query()->find($data['contract_id']);
            if ($contract !== null) {
                $data['order_id'] = $contract->order_id;
            }
        }
        if (($data['status'] ?? '') === 'Dispatched' && empty($data['dispatched_at'])) {
            $data['dispatched_at'] = now();
        }

        return $data;
    }
}
