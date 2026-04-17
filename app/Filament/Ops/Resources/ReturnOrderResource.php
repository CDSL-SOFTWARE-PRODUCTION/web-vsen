<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Inventory;
use App\Filament\Ops\Resources\ReturnOrderResource\Pages;
use App\Filament\Ops\Resources\ReturnOrderResource\RelationManagers\ReturnLinesRelationManager;
use App\Models\Demand\Order;
use App\Models\Supply\ReturnOrder;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnOrderResource extends Resource
{
    protected static ?string $model = ReturnOrder::class;

    protected static ?string $cluster = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.return_order.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label(__('ops.resources.order.navigation'))
                    ->options(fn (): array => Order::query()->orderByDesc('id')->pluck('order_code', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('return_code')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'Draft' => 'Draft',
                        'Approved' => 'Approved',
                        'Processing' => 'Processing',
                        'Closed' => 'Closed',
                        'Refunded' => 'Refunded',
                    ])
                    ->default('Draft'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('return_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.order_code')
                    ->label(__('ops.resources.order.navigation'))
                    ->placeholder('—')
                    ->url(fn (ReturnOrder $r): ?string => $r->order_id
                        ? OrderResource::getUrl('edit', ['record' => $r->order_id])
                        : null),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('lines_count')->counts('lines')->label(__('ops.resources.return_order.lines')),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Draft' => 'Draft',
                    'Approved' => 'Approved',
                    'Processing' => 'Processing',
                    'Closed' => 'Closed',
                    'Refunded' => 'Refunded',
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReturnLinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnOrders::route('/'),
            'create' => Pages\CreateReturnOrder::route('/create'),
            'edit' => Pages\EditReturnOrder::route('/{record}/edit'),
        ];
    }
}
