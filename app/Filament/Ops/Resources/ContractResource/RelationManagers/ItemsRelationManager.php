<?php

namespace App\Filament\Ops\Resources\ContractResource\RelationManagers;

use App\Models\Ops\ContractItem;
use App\Models\Ops\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.contract_items.title');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('spec')
                    ->rows(2),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\DatePicker::make('delivery_deadline')
                    ->required(),
                Forms\Components\Select::make('partner_id')
                    ->label(__('ops.common.vendor'))
                    ->options(Partner::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('lead_time_days')
                    ->required()
                    ->numeric()
                    ->default(7),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'not_ordered' => __('ops.contract_items.status.not_ordered'),
                        'vendor_confirmed' => __('ops.contract_items.status.vendor_confirmed'),
                        'inbound' => __('ops.contract_items.status.inbound'),
                        'ready_to_ship' => __('ops.contract_items.status.ready_to_ship'),
                        'delivered' => __('ops.contract_items.status.delivered'),
                        'accepted' => __('ops.contract_items.status.accepted'),
                    ]),
                Forms\Components\Select::make('docs_status')
                    ->required()
                    ->options([
                        'missing' => __('ops.common.docs_status.missing'),
                        'partial' => __('ops.common.docs_status.partial'),
                        'complete' => __('ops.common.docs_status.complete'),
                    ]),
                Forms\Components\Select::make('cash_status')
                    ->required()
                    ->options([
                        'not_needed' => __('ops.contract_items.cash_status.not_needed'),
                        'upcoming' => __('ops.contract_items.cash_status.upcoming'),
                        'need_fund' => __('ops.contract_items.cash_status.need_fund'),
                    ]),
                Forms\Components\Toggle::make('is_critical')
                    ->default(false),
                Forms\Components\Select::make('line_risk_level')
                    ->required()
                    ->options([
                        'Green' => __('ops.common.risk.green'),
                        'Amber' => __('ops.common.risk.amber'),
                        'Red' => __('ops.common.risk.red'),
                    ]),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('partner.name')
                    ->label(__('ops.common.vendor'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_deadline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.contract_items.status.' . $state)),
                Tables\Columns\TextColumn::make('docs_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.common.docs_status.' . $state)),
                Tables\Columns\TextColumn::make('cash_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.contract_items.cash_status.' . $state)),
                Tables\Columns\IconColumn::make('is_critical')
                    ->boolean(),
                Tables\Columns\TextColumn::make('line_risk_level')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.common.risk.' . strtolower($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'Red' => 'danger',
                        'Amber' => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('line_risk_level')
                    ->options([
                        'Red' => __('ops.common.risk.red'),
                        'Amber' => __('ops.common.risk.amber'),
                        'Green' => __('ops.common.risk.green'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('setRed')
                    ->label(__('ops.contract_items.actions.mark_red'))
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->action(fn (ContractItem $record): bool => $record->update(['line_risk_level' => 'Red'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
