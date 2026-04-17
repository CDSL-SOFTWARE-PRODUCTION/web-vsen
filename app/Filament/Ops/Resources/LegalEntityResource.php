<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\LegalEntityResource\Pages;
use App\Models\LegalEntity;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegalEntityResource extends Resource
{
    protected static ?string $model = LegalEntity::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.legal_entity.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('tax_code')->maxLength(50)->label(__('ops.resources.legal_entity.tax_code')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tax_code')->label(__('ops.resources.legal_entity.tax_code')),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalEntities::route('/'),
            'create' => Pages\CreateLegalEntity::route('/create'),
            'edit' => Pages\EditLegalEntity::route('/{record}/edit'),
        ];
    }
}
