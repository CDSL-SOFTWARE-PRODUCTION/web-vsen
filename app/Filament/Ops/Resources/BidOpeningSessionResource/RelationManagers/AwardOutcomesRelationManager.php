<?php

namespace App\Filament\Ops\Resources\BidOpeningSessionResource\RelationManagers;

use App\Models\Demand\TenderSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AwardOutcomesRelationManager extends RelationManager
{
    protected static string $relationship = 'awardOutcomes';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.award_outcome.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('source_system')
                ->required()
                ->default('muasamcong')
                ->maxLength(50),
            Forms\Components\TextInput::make('source_notify_no')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('lot_code')
                ->label(__('ops.award_outcome.fields.lot_code'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('winning_bidder_identifier')
                ->label(__('ops.award_outcome.fields.winning_bidder_identifier'))
                ->maxLength(255),
            Forms\Components\TextInput::make('winning_bidder_name')
                ->label(__('ops.award_outcome.fields.winning_bidder_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('winning_price')
                ->label(__('ops.award_outcome.fields.winning_price'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('currency')
                ->label(__('ops.award_outcome.fields.currency'))
                ->default('VND')
                ->maxLength(3),
            Forms\Components\DateTimePicker::make('awarded_at')
                ->label(__('ops.award_outcome.fields.awarded_at')),
            Forms\Components\TextInput::make('status')
                ->label(__('ops.award_outcome.fields.status'))
                ->default('awarded')
                ->required()
                ->maxLength(30),
            Forms\Components\Select::make('tender_snapshot_id')
                ->label(__('ops.award_outcome.fields.tender_snapshot'))
                ->searchable()
                ->preload()
                ->options(TenderSnapshot::query()->orderByDesc('id')->pluck('source_notify_no', 'id')),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('awarded_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('lot_code')
                    ->label(__('ops.award_outcome.columns.lot_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('winning_bidder_name')
                    ->label(__('ops.award_outcome.columns.winning_bidder_name'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('winning_price')
                    ->label(__('ops.award_outcome.columns.winning_price'))
                    ->money('VND'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.award_outcome.columns.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('awarded_at')
                    ->label(__('ops.award_outcome.columns.awarded_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
