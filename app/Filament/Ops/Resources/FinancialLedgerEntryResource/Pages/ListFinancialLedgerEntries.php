<?php

namespace App\Filament\Ops\Resources\FinancialLedgerEntryResource\Pages;

use App\Filament\Ops\Resources\FinancialLedgerEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListFinancialLedgerEntries extends ListRecords
{
    protected static string $resource = FinancialLedgerEntryResource::class;
}
