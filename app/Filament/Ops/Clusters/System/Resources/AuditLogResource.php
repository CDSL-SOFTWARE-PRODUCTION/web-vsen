<?php

namespace App\Filament\Ops\Clusters\System\Resources;

use App\Filament\Ops\Clusters\SystemCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\System\Resources\AuditLogResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\System\AuditLog;
use App\Support\Ops\FilamentAccess;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class AuditLogResource extends OpsResource
{
    protected static ?string $model = AuditLog::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.audit_log.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Schema::hasTable('audit_logs');
    }

    public static function canAccess(): bool
    {
        return Schema::hasTable('audit_logs');
    }

    public static function canViewAny(): bool
    {
        return static::canAccess() && FilamentAccess::allowRoles(FilamentAccess::ROLES_ADMIN_ONLY);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label(__('ops.audit_log.fields.actor'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('entity_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('entity_id'),
                Tables\Columns\TextColumn::make('action')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('context')
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state === null || $state === '') {
                            return '{}';
                        }
                        if (is_array($state)) {
                            return json_encode($state, JSON_UNESCAPED_UNICODE) ?: '{}';
                        }
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                return json_encode($decoded, JSON_UNESCAPED_UNICODE) ?: '{}';
                            }

                            return $state;
                        }

                        return '{}';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entity_type')
                    ->options([
                        'TenderSnapshot' => 'TenderSnapshot',
                        'Contract' => 'Contract',
                        'PaymentMilestone' => 'PaymentMilestone',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (! Schema::hasTable('audit_logs')) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery();
    }
}
