<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Supply;
use App\Filament\Ops\Resources\SupplyOrderResource\Pages;
use App\Models\LegalEntity;
use App\Models\Supply\SupplyOrder;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class SupplyOrderResource extends Resource
{
    protected static ?string $model = SupplyOrder::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Supply::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['order.legalEntity']);
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.supply_order.navigation');
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', SupplyOrder::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
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
                Tables\Filters\SelectFilter::make('legal_entity_id')
                    ->label(__('ops.supply_order.filters.legal_entity'))
                    ->options(fn (): array => LegalEntity::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas(
                            'order',
                            fn (Builder $q): Builder => $q->where('legal_entity_id', $data['value'])
                        );
                    }),
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
