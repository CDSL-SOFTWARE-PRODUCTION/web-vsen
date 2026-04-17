<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): ?string
    {
        return __('ops.nav_groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.user.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_ADMIN_ONLY);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.user.section.user_details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('off'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255)
                            ->autocomplete('new-password'),
                        Forms\Components\Select::make('role')
                            ->label(__('ops.user.role.label'))
                            ->options([
                                'Admin_PM' => __('ops.user.role.admin_pm'),
                                'Sale' => __('ops.user.role.sale'),
                                'MuaHang' => __('ops.user.role.mua_hang'),
                                'Kho' => __('ops.user.role.kho'),
                                'KeToan' => __('ops.user.role.ke_toan'),
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('legal_entity_id')
                            ->label(__('ops.user.legal_entity'))
                            ->relationship('legalEntity', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.user.role.'.match ($state) {
                        'Admin_PM' => 'admin_pm',
                        'Sale' => 'sale',
                        'MuaHang' => 'mua_hang',
                        'Kho' => 'kho',
                        'KeToan' => 'ke_toan',
                        default => 'label',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'Admin_PM' => 'danger',
                        'Sale' => 'warning',
                        'MuaHang' => 'info',
                        'Kho' => 'success',
                        'KeToan' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'Admin_PM' => __('ops.user.role.admin_pm'),
                        'Sale' => __('ops.user.role.sale'),
                        'MuaHang' => __('ops.user.role.mua_hang'),
                        'Kho' => __('ops.user.role.kho'),
                        'KeToan' => __('ops.user.role.ke_toan'),
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
