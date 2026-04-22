<?php

namespace App\Filament\Ops\Clusters\Finance\Resources\PaymentMilestoneResource\Pages;

use App\Filament\Ops\Resources\Base\Pages\OpsListRecords;
use App\Filament\Ops\Clusters\Finance\Resources\PaymentMilestoneResource;

class ListPaymentMilestones extends OpsListRecords
{
    protected static string $resource = PaymentMilestoneResource::class;
}
