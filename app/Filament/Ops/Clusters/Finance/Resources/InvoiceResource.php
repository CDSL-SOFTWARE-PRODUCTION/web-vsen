<?php

namespace App\Filament\Ops\Clusters\Finance\Resources;

use App\Filament\Ops\Clusters\FinanceCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Finance\Resources\InvoiceResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Ops\Contract;
use App\Models\Ops\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class InvoiceResource extends OpsResource
{
    protected static ?string $model = Invoice::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.invoice.navigation');
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', Invoice::class);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->label(__('ops.common.contract'))
                    ->required()
                    ->options(Contract::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->required()
                    ->prefix('VND'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_code')->searchable(),
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->money('VND', locale: 'vi'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('payment_due_date')->date(),
                Tables\Columns\TextColumn::make('days_overdue_cached')->label(__('ops.invoice.columns.days_overdue')),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === 'Admin_PM';
    }
}
