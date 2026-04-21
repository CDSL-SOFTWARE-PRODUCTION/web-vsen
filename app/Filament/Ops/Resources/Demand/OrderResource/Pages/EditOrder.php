<?php

namespace App\Filament\Ops\Resources\Demand\OrderResource\Pages;

use App\Filament\Ops\Resources\Demand\OrderResource;
use App\Filament\Ops\Resources\Base\HasDemandFlowSubheading;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditOrder extends EditRecord
{
    use HasDemandFlowSubheading;

    protected static string $resource = OrderResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->demandFlowSubheading(__('ops.flow.step_1_chip'), __('ops.order.flow_hint'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

