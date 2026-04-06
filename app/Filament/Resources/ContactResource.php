<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'CRM & Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('company')
                            ->label(__('Company'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subject')
                            ->label(__('Subject'))
                            ->columnSpanFull()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('message')
                            ->label(__('Message'))
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'new' => __('New'),
                                'read' => __('Read'),
                                'replied' => __('Replied'),
                            ])
                            ->default('new')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'read',
                        'success' => 'replied',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'new' => __('New'),
                        'read' => __('Read'),
                        'replied' => __('Replied'),
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
