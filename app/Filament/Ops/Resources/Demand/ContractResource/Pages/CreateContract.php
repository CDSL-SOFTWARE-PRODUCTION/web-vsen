<?php

namespace App\Filament\Ops\Resources\Demand\ContractResource\Pages;

use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Support\HasDemandFlowSubheading;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateContract extends CreateRecord
{
    use HasDemandFlowSubheading;

    protected static string $resource = ContractResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->demandFlowSubheading(__('ops.flow.step_2_chip'), __('ops.contract.flow_hint'));
    }
}
