<?php

namespace App\Filament\Ops\Resources\PaymentMilestoneResource\Pages;

use App\Filament\Ops\Resources\PaymentMilestoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMilestones extends ListRecords
{
    protected static string $resource = PaymentMilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
