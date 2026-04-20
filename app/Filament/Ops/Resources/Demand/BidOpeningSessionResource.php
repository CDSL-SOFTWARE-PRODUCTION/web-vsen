<?php

namespace App\Filament\Ops\Resources\Demand;

use App\Domain\Demand\CreateOrderFromBidOpeningSessionService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Demand\BidOpeningSessionResource\Pages;
use App\Filament\Ops\Resources\Demand\BidOpeningSessionResource\RelationManagers\AwardOutcomesRelationManager;
use App\Filament\Ops\Resources\Demand\BidOpeningSessionResource\RelationManagers\LinesRelationManager;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Demand\BidOpeningSession;
use App\Models\Demand\TenderSnapshot;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class BidOpeningSessionResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = BidOpeningSession::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $recordTitleAttribute = 'source_notify_no';

    protected static function opsNavigationClusterKey(): string
    {
        return 'demand';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.bid_opening_session.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.bid_opening_session.sections.core'))
                    ->schema([
                        Forms\Components\TextInput::make('source_system')
                            ->required()
                            ->default('muasamcong')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('source_notify_no')
                            ->label(__('ops.bid_opening_session.fields.source_notify_no'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_plan_no')
                            ->label(__('ops.bid_opening_session.fields.source_plan_no'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('session_version')
                            ->label(__('ops.bid_opening_session.fields.session_version'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Forms\Components\DateTimePicker::make('opened_at')
                            ->label(__('ops.bid_opening_session.fields.opened_at')),
                        Forms\Components\TextInput::make('total_bidders')
                            ->label(__('ops.bid_opening_session.fields.total_bidders'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\Select::make('tender_snapshot_id')
                            ->label(__('ops.bid_opening_session.fields.tender_snapshot'))
                            ->searchable()
                            ->preload()
                            ->options(
                                TenderSnapshot::query()
                                    ->orderByDesc('id')
                                    ->limit(200)
                                    ->pluck('source_notify_no', 'id')
                            ),
                        Forms\Components\TextInput::make('source_url')
                            ->label(__('ops.bid_opening_session.fields.source_url'))
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('source_notify_no')
                    ->label(__('ops.bid_opening_session.columns.tbmt'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_plan_no')
                    ->label(__('ops.bid_opening_session.columns.plan_no'))
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('session_version')
                    ->label(__('ops.bid_opening_session.columns.version'))
                    ->badge(),
                Tables\Columns\TextColumn::make('opened_at')
                    ->label(__('ops.bid_opening_session.columns.opened_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bidders')
                    ->label(__('ops.bid_opening_session.columns.total_bidders'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lines_count')
                    ->label(__('ops.bid_opening_session.columns.lines_count'))
                    ->counts('lines'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('create_order_projection')
                    ->label(__('ops.bid_opening_session.actions.create_order_projection'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->requiresConfirmation()
                    ->action(function (BidOpeningSession $record): void {
                        $result = app(CreateOrderFromBidOpeningSessionService::class)->handle(
                            $record->id,
                            auth()->id()
                        );

                        Notification::make()
                            ->title(__('ops.bid_opening_session.notifications.order_created'))
                            ->body(__('ops.bid_opening_session.notifications.order_created_body', [
                                'order_id' => $result['order_id'],
                                'items' => $result['order_items_count'],
                            ]))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LinesRelationManager::class,
            AwardOutcomesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBidOpeningSessions::route('/'),
            'create' => Pages\CreateBidOpeningSession::route('/create'),
            'view' => Pages\ViewBidOpeningSession::route('/{record}'),
            'edit' => Pages\EditBidOpeningSession::route('/{record}/edit'),
        ];
    }
}
