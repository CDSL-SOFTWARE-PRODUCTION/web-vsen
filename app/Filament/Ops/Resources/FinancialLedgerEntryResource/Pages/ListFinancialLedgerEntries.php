<?php

namespace App\Filament\Ops\Resources\FinancialLedgerEntryResource\Pages;

use App\Filament\Ops\Resources\FinancialLedgerEntryResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFinancialLedgerEntries extends ListRecords
{
    protected static string $resource = FinancialLedgerEntryResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('ops.financial_ledger.tabs.all')),
            'inflows' => Tab::make(__('ops.financial_ledger.tabs.inflows'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('amount', '>', 0)),
            'outflows' => Tab::make(__('ops.financial_ledger.tabs.outflows'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('amount', '<', 0)),
        ];
    }
}
