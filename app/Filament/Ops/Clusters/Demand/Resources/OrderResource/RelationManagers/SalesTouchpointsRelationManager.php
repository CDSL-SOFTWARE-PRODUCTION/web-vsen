<?php

namespace App\Filament\Ops\Clusters\Demand\Resources\OrderResource\RelationManagers;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\DemandCluster;

use App\Models\Ops\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesTouchpointsRelationManager extends RelationManager
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = DemandCluster::class;
    protected static string $relationship = 'salesTouchpoints';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.sales_touchpoint.navigation');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('partner_id')
                ->label(__('ops.sales_touchpoint.partner'))
                ->options(Partner::query()->pluck('name', 'id'))
                ->searchable()
                ->nullable(),
            Forms\Components\Select::make('activity_type')
                ->options([
                    'call' => 'Call',
                    'visit' => 'Visit',
                    'email' => 'Email',
                    'handover' => 'Handover',
                    'other' => 'Other',
                ])
                ->required(),
            Forms\Components\DateTimePicker::make('occurred_at')
                ->default(now())
                ->required(),
            Forms\Components\Textarea::make('summary')
                ->rows(3)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('activity_type'),
                Tables\Columns\TextColumn::make('summary')->limit(50),
                Tables\Columns\TextColumn::make('partner.name')->label(__('ops.sales_touchpoint.partner'))->placeholder('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by_user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
