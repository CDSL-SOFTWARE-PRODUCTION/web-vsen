<?php

namespace App\Filament\Ops\Resources\Demand;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Demand\ExecutionIssueResource\Pages;
use App\Filament\Ops\Resources\Demand\ExecutionIssueResource\RelationManagers\UpdatesRelationManager;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Ops\Contract;
use App\Models\Ops\ContractItem;
use App\Models\Ops\ExecutionIssue;
use App\Models\User;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class ExecutionIssueResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = ExecutionIssue::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

    protected static function opsNavigationClusterKey(): string
    {
        return 'demand';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.execution_issue.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->required()
                    ->options(Contract::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('contract_item_id')
                    ->label(__('ops.common.contract_item'))
                    ->options(ContractItem::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('issue_type')
                    ->required()
                    ->options([
                        'Delay' => __('ops.execution_issue.type.delay'),
                        'DocMissing' => __('ops.execution_issue.type.doc_missing'),
                        'Quality' => __('ops.execution_issue.type.quality'),
                        'ScopeChange' => __('ops.execution_issue.type.scope_change'),
                        'CashGap' => __('ops.execution_issue.type.cash_gap'),
                        'VendorSilence' => __('ops.execution_issue.type.vendor_silence'),
                        'Other' => __('ops.execution_issue.type.other'),
                    ]),
                Forms\Components\Select::make('severity')
                    ->required()
                    ->options([
                        'Low' => __('ops.execution_issue.severity.low'),
                        'Medium' => __('ops.execution_issue.severity.medium'),
                        'High' => __('ops.execution_issue.severity.high'),
                        'Critical' => __('ops.execution_issue.severity.critical'),
                    ]),
                Forms\Components\CheckboxList::make('impact_flags')
                    ->options([
                        'deadline' => __('ops.execution_issue.impact.deadline'),
                        'cost' => __('ops.execution_issue.impact.cost'),
                        'documents' => __('ops.execution_issue.impact.documents'),
                        'quality' => __('ops.execution_issue.impact.quality'),
                        'payment' => __('ops.execution_issue.impact.payment'),
                    ]),
                Forms\Components\Select::make('owner_user_id')
                    ->label(__('ops.common.owner'))
                    ->options(User::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('due_at'),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'Open' => __('ops.execution_issue.status.open'),
                        'InProgress' => __('ops.execution_issue.status.in_progress'),
                        'PendingApproval' => __('ops.execution_issue.status.pending_approval'),
                        'Resolved' => __('ops.execution_issue.status.resolved'),
                        'Cancelled' => __('ops.execution_issue.status.cancelled'),
                    ])
                    ->default('Open'),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
                Forms\Components\Textarea::make('resolution_note')
                    ->rows(3),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_at')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('contractItem.name')
                    ->label(__('ops.common.item'))
                    ->placeholder('-')
                    ->limit(25),
                Tables\Columns\TextColumn::make('issue_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.execution_issue.type.'.match ($state) {
                        'Delay' => 'delay',
                        'DocMissing' => 'doc_missing',
                        'Quality' => 'quality',
                        'ScopeChange' => 'scope_change',
                        'CashGap' => 'cash_gap',
                        'VendorSilence' => 'vendor_silence',
                        default => 'other',
                    })),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.execution_issue.severity.'.strtolower($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'Critical' => 'danger',
                        'High' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('ops.common.owner'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.execution_issue.status.'.match ($state) {
                        'Open' => 'open',
                        'InProgress' => 'in_progress',
                        'PendingApproval' => 'pending_approval',
                        'Resolved' => 'resolved',
                        default => 'cancelled',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'Resolved' => 'success',
                        'Cancelled' => 'gray',
                        'PendingApproval' => 'warning',
                        default => 'info',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('ops.common.status'))
                    ->options([
                        'Open' => __('ops.execution_issue.status.open'),
                        'InProgress' => __('ops.execution_issue.status.in_progress'),
                        'PendingApproval' => __('ops.execution_issue.status.pending_approval'),
                        'Resolved' => __('ops.execution_issue.status.resolved'),
                        'Cancelled' => __('ops.execution_issue.status.cancelled'),
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->label(__('ops.execution_issue.filters.severity'))
                    ->options([
                        'Low' => __('ops.execution_issue.severity.low'),
                        'Medium' => __('ops.execution_issue.severity.medium'),
                        'High' => __('ops.execution_issue.severity.high'),
                        'Critical' => __('ops.execution_issue.severity.critical'),
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->label(__('ops.execution_issue.filters.overdue'))
                    ->query(fn ($query) => $query
                        ->whereIn('status', ['Open', 'InProgress', 'PendingApproval'])
                        ->where('due_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\Action::make('toInProgress')
                    ->label(__('ops.execution_issue.actions.start'))
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (ExecutionIssue $record): bool => $record->status === 'Open')
                    ->action(fn (ExecutionIssue $record): bool => $record->update(['status' => 'InProgress'])),
                Tables\Actions\Action::make('toPendingApproval')
                    ->label(__('ops.execution_issue.actions.request_approval'))
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (ExecutionIssue $record): bool => $record->status === 'InProgress')
                    ->action(function (ExecutionIssue $record): void {
                        $record->update(['status' => 'PendingApproval']);
                        Notification::make()
                            ->title(__('ops.execution_issue.notifications.pending_approval'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('resolveIssue')
                    ->label(__('ops.execution_issue.actions.resolve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ExecutionIssue $record): bool => in_array($record->status, ['Open', 'InProgress', 'PendingApproval'], true))
                    ->action(function (ExecutionIssue $record): void {
                        $record->update([
                            'status' => 'Resolved',
                            'resolved_at' => now(),
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UpdatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExecutionIssues::route('/'),
            'create' => Pages\CreateExecutionIssue::route('/create'),
            'view' => Pages\ViewExecutionIssue::route('/{record}'),
            'edit' => Pages\EditExecutionIssue::route('/{record}/edit'),
        ];
    }
}
