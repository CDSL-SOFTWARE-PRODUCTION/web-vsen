<?php

namespace App\Filament\Ops\Resources\TenderSnapshotResource\RelationManagers;

use App\Models\Demand\TenderSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Attachments';

    private function isLocked(): bool
    {
        /** @var TenderSnapshot $snapshot */
        $snapshot = $this->getOwnerRecord();

        return $snapshot->isLocked();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_path')
                    ->helperText('MVP: lưu path nội bộ hoặc S3 key. (Upload thật sẽ làm sau)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('external_url')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('mime_type')
                    ->maxLength(100),
                Forms\Components\TextInput::make('file_size_bytes')
                    ->numeric(),
            ])
            ->columns(2)
            ->disabled(fn (): bool => $this->isLocked());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->placeholder('-')
                    ->limit(30),
                Tables\Columns\TextColumn::make('file_path')
                    ->placeholder('-')
                    ->limit(40),
                Tables\Columns\TextColumn::make('external_url')
                    ->placeholder('-')
                    ->limit(40),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => !$this->isLocked()),
                ]),
            ]);
    }
}

