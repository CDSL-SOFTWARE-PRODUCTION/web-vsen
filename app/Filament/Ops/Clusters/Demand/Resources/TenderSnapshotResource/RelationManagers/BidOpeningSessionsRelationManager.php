<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\TenderSnapshotResource\RelationManagers;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\DemandCluster;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BidOpeningSessionsRelationManager extends RelationManager
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = DemandCluster::class;
    protected static string $relationship = 'bidOpeningSessions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.bid_opening_session.navigation');
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
            Forms\Components\TextInput::make('source_plan_no')
                ->maxLength(255),
            Forms\Components\TextInput::make('session_version')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),
            Forms\Components\DateTimePicker::make('opened_at'),
            Forms\Components\TextInput::make('total_bidders')
                ->numeric()
                ->default(0),
            Forms\Components\TextInput::make('source_url')
                ->url()
                ->maxLength(255),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('session_version')
                    ->badge(),
                Tables\Columns\TextColumn::make('opened_at')
                    ->dateTime()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('total_bidders')
                    ->numeric(),
                Tables\Columns\TextColumn::make('lines_count')
                    ->counts('lines')
                    ->label(__('ops.bid_opening_session.columns.lines_count')),
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
