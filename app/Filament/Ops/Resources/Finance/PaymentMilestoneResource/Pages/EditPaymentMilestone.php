<?php

namespace App\Filament\Ops\Resources\Finance\PaymentMilestoneResource\Pages;

use App\Filament\Ops\Resources\Finance\PaymentMilestoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentMilestone extends EditRecord
{
    protected static string $resource = PaymentMilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
