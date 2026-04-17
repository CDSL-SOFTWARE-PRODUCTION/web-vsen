<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Demand;
use App\Filament\Ops\Resources\DocumentResource\Pages;
use App\Models\Ops\Contract;
use App\Models\Ops\ContractItem;
use App\Models\Ops\Document;
use App\Models\Ops\PaymentMilestone;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Demand::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.document.navigation');
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
                Forms\Components\Select::make('payment_milestone_id')
                    ->label(__('ops.common.payment_milestone'))
                    ->options(PaymentMilestone::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('document_group')
                    ->required()
                    ->options([
                        'source' => __('ops.document.group.source'),
                        'quality_legal' => __('ops.document.group.quality_legal'),
                        'delivery_install' => __('ops.document.group.delivery_install'),
                        'acceptance_payment' => __('ops.document.group.acceptance_payment'),
                    ]),
                Forms\Components\TextInput::make('document_type')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'missing' => __('ops.document.status.missing'),
                        'uploaded' => __('ops.document.status.uploaded'),
                        'validated' => __('ops.document.status.validated'),
                    ]),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\TextInput::make('file_path')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expiry_date')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('contractItem.name')
                    ->label(__('ops.common.item'))
                    ->placeholder('-')
                    ->limit(25),
                Tables\Columns\TextColumn::make('paymentMilestone.name')
                    ->label(__('ops.common.milestone'))
                    ->placeholder('-')
                    ->limit(20),
                Tables\Columns\TextColumn::make('document_group')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.document.group.'.$state)),
                Tables\Columns\TextColumn::make('document_type')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.document.status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'missing' => 'danger',
                        'uploaded' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->color(fn ($state): string => $state && now()->diffInDays($state, false) <= 15 ? 'danger' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('ops.common.status'))
                    ->options([
                        'missing' => __('ops.document.status.missing'),
                        'uploaded' => __('ops.document.status.uploaded'),
                        'validated' => __('ops.document.status.validated'),
                    ]),
                Tables\Filters\SelectFilter::make('document_group')
                    ->label(__('ops.document.filters.document_group'))
                    ->options([
                        'source' => __('ops.document.group.source'),
                        'quality_legal' => __('ops.document.group.quality_legal'),
                        'delivery_install' => __('ops.document.group.delivery_install'),
                        'acceptance_payment' => __('ops.document.group.acceptance_payment'),
                    ]),
                Tables\Filters\Filter::make('expiring_30d')
                    ->label(__('ops.document.filters.expiring_30d'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('expiry_date')
                        ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])),
            ])
            ->actions([
                Tables\Actions\Action::make('markValidated')
                    ->label(__('ops.document.actions.validate'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Document $record): bool => $record->update(['status' => 'validated'])),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
