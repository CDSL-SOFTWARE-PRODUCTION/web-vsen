<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Finance;
use App\Filament\Ops\Resources\CashPlanEventResource\Pages;
use App\Models\Ops\CashPlanEvent;
use App\Models\Ops\Contract;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class CashPlanEventResource extends Resource
{
    protected static ?string $model = CashPlanEvent::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Finance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.cash_plan_event.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_DEMAND_EXTENDED);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->options(Contract::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('partner_id')
                    ->label(__('ops.common.vendor'))
                    ->options(Partner::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('scheduled_date')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('VND'),
                Forms\Components\Select::make('purpose')
                    ->required()
                    ->options([
                        'PaySupplier' => __('ops.cash_plan_event.purpose.pay_supplier'),
                        'Customs' => __('ops.cash_plan_event.purpose.customs'),
                        'Logistics' => __('ops.cash_plan_event.purpose.logistics'),
                        'InternalTransfer' => __('ops.cash_plan_event.purpose.internal_transfer'),
                        'Other' => __('ops.cash_plan_event.purpose.other'),
                    ]),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_date')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('partner.name')
                    ->label(__('ops.common.vendor'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('VND', locale: 'vi')
                    ->summarize(Sum::make()->money('VND', locale: 'vi')),
                Tables\Columns\TextColumn::make('purpose')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.cash_plan_event.purpose.'.match ($state) {
                        'PaySupplier' => 'pay_supplier',
                        'Customs' => 'customs',
                        'Logistics' => 'logistics',
                        'InternalTransfer' => 'internal_transfer',
                        default => 'other',
                    })),
            ])
            ->filters([
                Tables\Filters\Filter::make('next_7_days')
                    ->label(__('ops.cash_plan_event.filters.next_7_days'))
                    ->query(fn ($query) => $query
                        ->whereBetween('scheduled_date', [now()->toDateString(), now()->addDays(7)->toDateString()])),
                Tables\Filters\Filter::make('next_14_days')
                    ->label(__('ops.cash_plan_event.filters.next_14_days'))
                    ->query(fn ($query) => $query
                        ->whereBetween('scheduled_date', [now()->toDateString(), now()->addDays(14)->toDateString()])),
                Tables\Filters\Filter::make('next_30_days')
                    ->label(__('ops.cash_plan_event.filters.next_30_days'))
                    ->query(fn ($query) => $query
                        ->whereBetween('scheduled_date', [now()->toDateString(), now()->addDays(30)->toDateString()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashPlanEvents::route('/'),
            'create' => Pages\CreateCashPlanEvent::route('/create'),
            'edit' => Pages\EditCashPlanEvent::route('/{record}/edit'),
        ];
    }
}
