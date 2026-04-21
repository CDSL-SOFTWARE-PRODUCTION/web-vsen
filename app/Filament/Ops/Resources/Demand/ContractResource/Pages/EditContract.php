<?php

namespace App\Filament\Ops\Resources\Demand\ContractResource\Pages;

use App\Filament\Ops\Resources\Demand\ContractResource;
use App\Filament\Ops\Resources\Base\HasDemandFlowSubheading;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditContract extends EditRecord
{
    use HasDemandFlowSubheading;

    protected static string $resource = ContractResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return $this->demandFlowSubheading(__('ops.flow.step_2_chip'), __('ops.contract.flow_hint'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
