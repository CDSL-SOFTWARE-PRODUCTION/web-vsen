<?php

namespace App\Filament\Ops\Resources\Demand;

use App\Domain\Demand\AbandonTenderCommandService;
use App\Domain\Demand\CloseContractCommandService;
use App\Domain\Demand\ConfirmContractCommandService;
use App\Domain\Demand\ConfirmFulfillmentCommandService;
use App\Domain\Demand\StartExecutionCommandService;
use App\Domain\Supply\GenerateSupplyOrderFromOrderService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Demand\OrderResource\Pages;
use App\Filament\Ops\Resources\Demand\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Ops\Resources\Demand\OrderResource\RelationManagers\SalesTouchpointsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Demand\Order;
use App\Models\Demand\TenderSnapshot;
use App\Models\LegalEntity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class OrderResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = Order::class;

    protected static ?string $slug = 'demand/orders';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = -80;

    protected static ?string $recordTitleAttribute = 'order_code';

    protected static function opsNavigationClusterKey(): string
    {
        return 'demand';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.order.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', Order::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('ops.order.section.order_info'))
                ->schema([
                    Forms\Components\TextInput::make('order_code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->hintIcon('heroicon-m-information-circle', __('ops.order.fields.order_code_helper')),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('legal_entity_id')
                        ->label(__('ops.order.fields.legal_entity'))
                        ->options(fn (): array => LegalEntity::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('state')
                        ->label(__('ops.order.fields.state'))
                        ->visibleOn('edit')
                        ->hintIcon('heroicon-m-information-circle')
                        ->hintIconTooltip(__('ops.order.fields.state_helper'))
                        ->disabled()
                        ->dehydrated(false)
                        ->options([
                            'SubmitTender' => 'SubmitTender',
                            'AwardTender' => 'AwardTender',
                            'ConfirmContract' => 'ConfirmContract',
                            'StartExecution' => 'StartExecution',
                            'Fulfilled' => 'Fulfilled',
                            'ContractClosed' => 'ContractClosed',
                            'Abandoned' => 'Abandoned',
                        ]),
                    Forms\Components\Select::make('tender_snapshot_id')
                        ->label(__('ops.order.fields.tender_snapshot'))
                        ->options(TenderSnapshot::query()->pluck('source_notify_no', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                            if (! is_numeric($state)) {
                                return;
                            }

                            $tbmt = TenderSnapshot::query()->whereKey((int) $state)->value('source_notify_no');
                            $currentOrderCode = (string) ($get('order_code') ?? '');
                            if ($tbmt === null || $tbmt === '') {
                                return;
                            }

                            $isEmpty = trim($currentOrderCode) === '';
                            $isGenerated = str_starts_with($currentOrderCode, 'ORD-');
                            if ($isEmpty || $isGenerated) {
                                $set('order_code', Order::buildOrderCodeFromTbmt($tbmt));
                            }

                            $currentName = (string) ($get('name') ?? '');
                            if (trim($currentName) === '' || str_starts_with($currentName, 'Order from')) {
                                $set('name', 'Order from '.$tbmt);
                            }
                        }),
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
                Tables\Columns\TextColumn::make('legalEntity.name')
                    ->label(__('ops.order.fields.legal_entity'))
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ContractClosed' => 'success',
                        'Fulfilled' => 'success',
                        'StartExecution' => 'success',
                        'ConfirmContract' => 'info',
                        'Abandoned' => 'danger',
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
                Tables\Actions\Action::make('confirmContract')
                    ->label(__('ops.order.actions.confirm_contract'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->state === 'AwardTender')
                    ->action(function (Order $record): void {
                        $result = app(ConfirmContractCommandService::class)->handle($record->id, auth()->id());

                        Notification::make()
                            ->title(__('ops.order.notifications.moved_confirm_contract'))
                            ->body(
                                $result->warningRaised
                                    ? implode("\n", $result->warnings)
                                    : __('ops.order.notifications.transition_ok')
                            )
                            ->color($result->warningRaised ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('generateSupplyOrder')
                    ->label(__('ops.order.actions.generate_supply_order'))
                    ->icon('heroicon-o-inbox')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->disabled(fn (Order $record): bool => $record->supplyOrders()->exists())
                    ->tooltip(fn (Order $record): ?string => $record->supplyOrders()->exists() ? __('ops.order.notifications.generate_supply_order_duplicate') : null)
                    ->action(function (Order $record): void {
                        try {
                            $result = app(GenerateSupplyOrderFromOrderService::class)->handle($record->id, auth()->id());
                        } catch (RuntimeException $exception) {
                            Notification::make()
                                ->title(__('ops.order.notifications.generate_supply_order_blocked'))
                                ->body($exception->getMessage())
                                ->actions([
                                    NotificationAction::make('openTenderLineRequirements')
                                        ->label(__('ops.order.notifications.open_tender_line_requirements'))
                                        ->button()
                                        ->url(TenderLineRequirementResource::getUrl('index')),
                                ])
                                ->color('warning')
                                ->send();

                            return;
                        }

                        $body = $result->supplyOrderId !== null
                            ? __('ops.order.notifications.supply_order_created', [
                                'supply_order_id' => $result->supplyOrderId,
                                'lines' => $result->shortageLinesCount,
                            ])
                            : __('ops.order.notifications.supply_order_not_needed');

                        Notification::make()
                            ->title(__('ops.order.notifications.generate_supply_order_done'))
                            ->body($body)
                            ->color('success')
                            ->send();
                    }),
                Tables\Actions\Action::make('startExecution')
                    ->label(__('ops.order.actions.start_execution'))
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->state === 'ConfirmContract')
                    ->action(function (Order $record): void {
                        $result = app(StartExecutionCommandService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.order.notifications.moved_start_execution'))
                            ->body(
                                $result->warningRaised
                                    ? implode("\n", $result->warnings)
                                    : __('ops.order.notifications.transition_ok')
                            )
                            ->color($result->warningRaised ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('confirmFulfillment')
                    ->label(__('ops.order.actions.confirm_fulfillment'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->state === 'StartExecution')
                    ->action(function (Order $record): void {
                        $result = app(ConfirmFulfillmentCommandService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.order.notifications.moved_fulfilled'))
                            ->body(
                                $result->warningRaised
                                    ? implode("\n", $result->warnings)
                                    : __('ops.order.notifications.transition_ok')
                            )
                            ->color($result->warningRaised ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('closeContract')
                    ->label(__('ops.order.actions.close_contract'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->state === 'Fulfilled')
                    ->action(function (Order $record): void {
                        $result = app(CloseContractCommandService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.order.notifications.moved_contract_closed'))
                            ->body(
                                $result->warningRaised
                                    ? implode("\n", $result->warnings)
                                    : __('ops.order.notifications.transition_ok')
                            )
                            ->color($result->warningRaised ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('abandonTender')
                    ->label(__('ops.order.actions.abandon_tender'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->state === 'SubmitTender')
                    ->action(function (Order $record): void {
                        $result = app(AbandonTenderCommandService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.order.notifications.moved_abandoned'))
                            ->body(
                                $result->warningRaised
                                    ? implode("\n", $result->warnings)
                                    : __('ops.order.notifications.transition_ok')
                            )
                            ->color($result->warningRaised ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            SalesTouchpointsRelationManager::class,
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
