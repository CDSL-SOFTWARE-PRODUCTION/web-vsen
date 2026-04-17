<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Supply;
use App\Filament\Ops\Resources\SupplyOrderResource\Pages;
use App\Models\Supply\SupplyOrder;
use App\Support\Ops\FilamentAccess;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplyOrderResource extends Resource
{
    protected static ?string $model = SupplyOrder::class;

    protected static ?string $cluster = Supply::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.supply_order.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(['Admin_PM', 'MuaHang', 'Sale', 'Kho']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $q): Builder => $q->with(['order.legalEntity']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('supply_order_code')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('order.order_code')
                    ->label(__('ops.resources.order.navigation'))
                    ->url(fn (SupplyOrder $r): string => OrderResource::getUrl('edit', ['record' => $r->order_id])),
                Tables\Columns\TextColumn::make('order.legalEntity.name')->label('Legal entity')->toggleable(),
                Tables\Columns\TextColumn::make('lines_count')->counts('lines')->label('Lines'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Open' => 'Open',
                        'PartiallyReceived' => 'PartiallyReceived',
                        'Received' => 'Received',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open_order')
                    ->label('Order')
                    ->url(fn (SupplyOrder $r): string => OrderResource::getUrl('edit', ['record' => $r->order_id])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplyOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
