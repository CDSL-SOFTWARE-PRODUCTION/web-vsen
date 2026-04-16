<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Demand;
use App\Filament\Ops\Resources\OrderResource\Pages;
use App\Filament\Ops\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $cluster = Demand::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'order_code';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.order.navigation');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('ops.order.section.order_info'))
                ->schema([
                    Forms\Components\TextInput::make('order_code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('state')
                        ->required()
                        ->options([
                            'SubmitTender' => 'SubmitTender',
                            'AwardTender' => 'AwardTender',
                            'ConfirmContract' => 'ConfirmContract',
                            'StartExecution' => 'StartExecution',
                        ]),
                    Forms\Components\Select::make('tender_snapshot_id')
                        ->label(__('ops.order.fields.tender_snapshot'))
                        ->options(TenderSnapshot::query()->pluck('source_notify_no', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'StartExecution' => 'success',
                        'ConfirmContract' => 'info',
                        'AwardTender' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('snapshot.source_notify_no')
                    ->label(__('ops.order.fields.tender_snapshot'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('ops.order.fields.items_count')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

