<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Demand;
use App\Filament\Ops\Resources\TenderLineRequirementResource\Pages;
use App\Models\Demand\TenderSnapshotItem;
use App\Models\Demand\TenderSnapshotItemRequirement;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenderLineRequirementResource extends Resource
{
    protected static ?string $model = TenderSnapshotItemRequirement::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Demand::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 25;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.tender_line_requirement.navigation');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tenderSnapshotItem.snapshot', 'requirement']);
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tender_snapshot_item_id')
                    ->label(__('ops.resources.tender_line_requirement.snapshot_line'))
                    ->options(function (): array {
                        return TenderSnapshotItem::query()
                            ->with('snapshot')
                            ->orderByDesc('tender_snapshot_id')
                            ->orderBy('line_no')
                            ->limit(2000)
                            ->get()
                            ->mapWithKeys(function (TenderSnapshotItem $row): array {
                                $no = $row->snapshot?->source_notify_no ?? '?';

                                return [
                                    $row->id => '#'.$row->id.' · '.$no.' · L'.$row->line_no.' — '.str($row->name)->limit(48),
                                ];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('requirement_id')
                    ->label(__('ops.resources.requirement.navigation'))
                    ->relationship('requirement', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenderSnapshotItem.snapshot.source_notify_no')
                    ->label('TBMT')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('tenderSnapshotItem.line_no')->label('Line'),
                Tables\Columns\TextColumn::make('tenderSnapshotItem.name')->limit(32),
                Tables\Columns\TextColumn::make('requirement.code')->label(__('ops.resources.requirement.code')),
                Tables\Columns\TextColumn::make('requirement.type')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('requirement_id')
                    ->relationship('requirement', 'code')
                    ->label(__('ops.resources.requirement.navigation')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenderLineRequirements::route('/'),
            'create' => Pages\CreateTenderLineRequirement::route('/create'),
            'edit' => Pages\EditTenderLineRequirement::route('/{record}/edit'),
        ];
    }
}
