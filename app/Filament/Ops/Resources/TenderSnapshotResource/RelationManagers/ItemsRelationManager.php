<?php

namespace App\Filament\Ops\Resources\TenderSnapshotResource\RelationManagers;

use App\Models\Demand\TenderSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items (2A)';

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
                Forms\Components\TextInput::make('line_no')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('uom')
                    ->label('Đơn vị tính')
                    ->maxLength(50),
                Forms\Components\TextInput::make('quantity_awarded')
                    ->label('Khối lượng')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tender_item_ref')
                    ->label('Ký mã hiệu')
                    ->maxLength(255),
                Forms\Components\TextInput::make('brand')
                    ->label('Nhãn hiệu')
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer')
                    ->label('Hãng sản xuất')
                    ->maxLength(255),
                Forms\Components\TextInput::make('origin_country')
                    ->label('Xuất xứ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacture_year')
                    ->label('Năm sản xuất')
                    ->numeric(),
                Forms\Components\Textarea::make('spec_committed_raw')
                    ->label('Thông số kỹ thuật (raw)')
                    ->rows(6),
                Forms\Components\TextInput::make('project_site')
                    ->label('Địa điểm dự án')
                    ->maxLength(255),
                Forms\Components\Textarea::make('delivery_earliest_rule')
                    ->label('Ngày giao sớm nhất (rule)')
                    ->rows(2),
                Forms\Components\Textarea::make('delivery_latest_rule')
                    ->label('Ngày giao muộn nhất (rule)')
                    ->rows(2),
                Forms\Components\Textarea::make('other_requirements_raw')
                    ->label('Yêu cầu khác')
                    ->rows(2),
            ])
            ->columns(2)
            ->disabled(fn (): bool => $this->isLocked());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('line_no')
            ->columns([
                Tables\Columns\TextColumn::make('line_no')
                    ->label('STT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Danh mục hàng hóa')
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('uom')
                    ->label('ĐVT')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity_awarded')
                    ->label('KL')
                    ->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('tender_item_ref')
                    ->label('Ký mã hiệu')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_latest_rule')
                    ->label('Giao muộn nhất (rule)')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30)
                    ->placeholder('-'),
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

