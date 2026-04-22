<?php

namespace App\Filament\Ops\Resources\Finance\PaymentMilestoneResource\Pages;

use App\Filament\Ops\Resources\Base\Pages\OpsListRecords;
use App\Filament\Ops\Resources\Finance\PaymentMilestoneResource;

class ListPaymentMilestones extends OpsListRecords
{
    protected static string $resource = PaymentMilestoneResource::class;
}
