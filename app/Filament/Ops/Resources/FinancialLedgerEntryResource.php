<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Finance;
use App\Filament\Ops\Resources\FinancialLedgerEntryResource\Pages;
use App\Models\Ops\FinancialLedgerEntry;
use App\Support\Ops\FilamentAccess;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialLedgerEntryResource extends Resource
{
    protected static ?string $model = FinancialLedgerEntry::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Finance::class;

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
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.invoice_code')
                    ->label(__('ops.invoice.columns.code'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('VND', locale: 'vi'),
                Tables\Columns\TextColumn::make('memo')->limit(40),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
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
