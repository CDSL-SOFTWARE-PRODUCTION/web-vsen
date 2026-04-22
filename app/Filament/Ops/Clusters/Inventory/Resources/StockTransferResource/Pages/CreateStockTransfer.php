<?php

namespace App\Filament\Ops\Clusters\Inventory\Resources\StockTransferResource\Pages;

use App\Filament\Ops\Resources\Base\Pages\OpsCreateRecord;
use App\Filament\Ops\Clusters\Inventory\Resources\StockTransferResource;

class CreateStockTransfer extends OpsCreateRecord
{
    protected static string $resource = StockTransferResource::class;
}
