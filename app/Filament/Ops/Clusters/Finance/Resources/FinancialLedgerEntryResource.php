<?php

namespace App\Filament\Ops\Clusters\Finance\Resources;

use App\Filament\Ops\Clusters\FinanceCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Finance\Resources\FinancialLedgerEntryResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource;
use App\Models\Ops\Contract;
use App\Models\Ops\FinancialLedgerEntry;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialLedgerEntryResource extends OpsResource
{
    protected static ?string $model = FinancialLedgerEntry::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.financial_ledger.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_FINANCE);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $q): Builder => $q->with(['contract', 'invoice', 'partner']))
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable()
                    ->url(fn (FinancialLedgerEntry $record): ?string => $record->contract_id
                        ? ContractResource::getUrl('view', ['record' => $record->contract_id])
                        : null),
                Tables\Columns\TextColumn::make('invoice.invoice_code')
                    ->label(__('ops.invoice.columns.code'))
                    ->placeholder('—')
                    ->url(fn (FinancialLedgerEntry $record): ?string => $record->invoice_id
                        ? InvoiceResource::getUrl('view', ['record' => $record->invoice_id])
                        : null),
                Tables\Columns\TextColumn::make('partner.name')
                    ->label(__('ops.financial_ledger.columns.counterparty'))
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('VND', locale: 'vi'),
                Tables\Columns\TextColumn::make('memo')->limit(40),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\Filter::make('payables_angle')
                    ->label(__('ops.financial_ledger.filters.outflows'))
                    ->query(fn (Builder $q): Builder => $q->where('amount', '<', 0)),
                Tables\Filters\Filter::make('receivables_angle')
                    ->label(__('ops.financial_ledger.filters.inflows'))
                    ->query(fn (Builder $q): Builder => $q->where('amount', '>', 0)),
                Tables\Filters\SelectFilter::make('contract_id')
                    ->label(__('ops.common.contract'))
                    ->options(fn (): array => Contract::query()->orderBy('contract_code')->pluck('contract_code', 'id')->all())
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialLedgerEntries::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
