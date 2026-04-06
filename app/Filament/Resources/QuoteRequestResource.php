<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Filament\Resources\QuoteRequestResource\RelationManagers;
use App\Models\QuoteRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'CRM & Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label(__('Product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload(),

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

                        Forms\Components\TextInput::make('quantity')
                            ->label(__('Quantity'))
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\Textarea::make('message')
                            ->label(__('Message'))
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'new' => __('New'),
                                'processing' => __('Processing'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
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
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'gray' => 'cancelled',
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
                        'processing' => __('Processing'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
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
            'index' => Pages\ListQuoteRequests::route('/'),
            'create' => Pages\CreateQuoteRequest::route('/create'),
            'edit' => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
