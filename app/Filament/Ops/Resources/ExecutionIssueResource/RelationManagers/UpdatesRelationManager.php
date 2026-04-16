<?php

namespace App\Filament\Ops\Resources\ExecutionIssueResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.issue_updates.title');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('ops.issue_updates.updated_by'))
                    ->options(User::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('status_from')
                    ->maxLength(30),
                Forms\Components\TextInput::make('status_to')
                    ->maxLength(30),
                Forms\Components\Textarea::make('note')
                    ->rows(3),
                Forms\Components\TextInput::make('attachment_path')
                    ->maxLength(255),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status_to')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('ops.issue_updates.by'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status_from')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status_to')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('note')
                    ->limit(35),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
