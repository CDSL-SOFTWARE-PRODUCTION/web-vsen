<?php

namespace App\Filament\Ops\Resources\System;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Filament\Ops\Resources\System\FounderWorkCardResource\Pages;
use App\Models\Ops\FounderWorkCard;
use App\Models\User;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class FounderWorkCardResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = FounderWorkCard::class;

    protected static ?string $slug = 'system/founder-work-cards';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 52;

    protected static function opsNavigationClusterKey(): string
    {
        return 'system';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.founder_work_card.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.founder_work_card.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.founder_work_card.plural_model_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (! Schema::hasTable('founder_work_cards')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function canViewAny(): bool
    {
        return Schema::hasTable('founder_work_cards')
            && FilamentAccess::allowRoles(FilamentAccess::ROLES_ADMIN_ONLY);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.founder_work_card.section.card'))
                    ->schema([
                        Forms\Components\Select::make('founder_user_id')
                            ->label(__('ops.founder_work_card.fields.founder_user'))
                            ->options(fn (): array => User::query()
                                ->where('role', 'Founder')
                                ->orderBy('email')
                                ->get()
                                ->mapWithKeys(fn (User $u): array => [
                                    $u->id => $u->name.' <'.$u->email.'>',
                                ])
                                ->all())
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label(__('ops.founder_work_card.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('summary')
                            ->label(__('ops.founder_work_card.fields.summary'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('assignee_label')
                            ->label(__('ops.founder_work_card.fields.assignee_label'))
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->label(__('ops.founder_work_card.fields.due_at'))
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label(__('ops.founder_work_card.fields.status'))
                            ->options([
                                FounderWorkCard::STATUS_OPEN => __('ops.founder_work_card.status.open'),
                                FounderWorkCard::STATUS_DONE => __('ops.founder_work_card.status.done'),
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('digest_lane')
                            ->label(__('ops.founder_work_card.fields.digest_lane'))
                            ->options([
                                FounderWorkCard::LANE_SIGNATURE => __('ops.founder_work_card.digest_lane.signature'),
                                FounderWorkCard::LANE_REPLY => __('ops.founder_work_card.digest_lane.reply'),
                                FounderWorkCard::LANE_GENERAL => __('ops.founder_work_card.digest_lane.general'),
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TagsInput::make('attachment_urls')
                            ->label(__('ops.founder_work_card.fields.attachment_urls'))
                            ->placeholder(__('ops.founder_work_card.fields.attachment_urls_placeholder'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_at')
            ->columns([
                Tables\Columns\TextColumn::make('founder.email')
                    ->label(__('ops.founder_work_card.fields.founder_user'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('digest_lane')
                    ->label(__('ops.founder_work_card.fields.digest_lane'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        FounderWorkCard::LANE_SIGNATURE => __('ops.founder_work_card.digest_lane.signature'),
                        FounderWorkCard::LANE_REPLY => __('ops.founder_work_card.digest_lane.reply'),
                        default => __('ops.founder_work_card.digest_lane.general'),
                    }),
                Tables\Columns\TextColumn::make('assignee_label')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        FounderWorkCard::STATUS_DONE => __('ops.founder_work_card.status.done'),
                        default => __('ops.founder_work_card.status.open'),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('digest_lane')
                    ->label(__('ops.founder_work_card.fields.digest_lane'))
                    ->options([
                        FounderWorkCard::LANE_SIGNATURE => __('ops.founder_work_card.digest_lane.signature'),
                        FounderWorkCard::LANE_REPLY => __('ops.founder_work_card.digest_lane.reply'),
                        FounderWorkCard::LANE_GENERAL => __('ops.founder_work_card.digest_lane.general'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('ops.founder_work_card.fields.status'))
                    ->options([
                        FounderWorkCard::STATUS_OPEN => __('ops.founder_work_card.status.open'),
                        FounderWorkCard::STATUS_DONE => __('ops.founder_work_card.status.done'),
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListFounderWorkCards::route('/'),
            'create' => Pages\CreateFounderWorkCard::route('/create'),
            'edit' => Pages\EditFounderWorkCard::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (! Schema::hasTable('founder_work_cards')) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()->with(['founder']);
    }
}
