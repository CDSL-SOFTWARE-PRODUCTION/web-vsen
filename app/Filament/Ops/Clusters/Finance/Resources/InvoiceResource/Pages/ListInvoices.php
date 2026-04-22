<?php

namespace App\Filament\Ops\Clusters\Finance\Resources\InvoiceResource\Pages;


use App\Filament\Ops\Resources\Base\Pages\OpsListRecords;
use App\Filament\Ops\Clusters\Finance\Resources\InvoiceResource;

class ListInvoices extends OpsListRecords
{

    protected static string $resource = InvoiceResource::class;
}
