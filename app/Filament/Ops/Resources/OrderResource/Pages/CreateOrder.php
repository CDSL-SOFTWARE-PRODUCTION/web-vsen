<?php

namespace App\Filament\Ops\Resources\OrderResource\Pages;

use App\Filament\Ops\Resources\OrderResource;
use App\Filament\Ops\Resources\Support\HasDemandFlowSubheading;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateOrder extends CreateRecord
{
    use HasDemandFlowSubheading;

    protected static string $resource = OrderResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->demandFlowSubheading(__('ops.flow.step_1_chip'), __('ops.order.flow_hint'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user !== null && $user->legal_entity_id !== null) {
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
