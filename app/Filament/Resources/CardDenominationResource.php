<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardDenominationResource\Pages;
use App\Filament\Resources\CardDenominationResource\RelationManagers;
use App\Models\CardDenomination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CardDenominationResource extends Resource
{
    protected static ?string $model = CardDenomination::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Mệnh giá';
    protected static ?string $navigationGroup = 'Quản lý thẻ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label('Loại thẻ')
                ->relationship('category', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label('Tên loại thẻ')
                        ->required(),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required(),
                ])
                ->placeholder('Chọn loại thẻ'),
            
            Forms\Components\TextInput::make('value')
                ->label('Mệnh giá')
                ->required()
                ->numeric()
                ->prefix('VNĐ')
                ->placeholder('Ví dụ: 50000'),
            
            Forms\Components\TextInput::make('price')
                ->label('Giá bán')
                ->required()
                ->numeric()
                ->prefix('VNĐ')
                ->placeholder('Ví dụ: 52500')
                ->helperText('Giá bán thường cao hơn mệnh giá 5-10%'),
            
            Forms\Components\TextInput::make('stock')
                ->label('Tồn kho')
                ->required()
                ->numeric()
                ->default(0)
                ->helperText('Số lượng thẻ có sẵn'),
            
            Forms\Components\Toggle::make('is_active')
                ->label('Kích hoạt')
                ->default(true)
                ->helperText('Tắt để ẩn mệnh giá này khỏi frontend'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Loại thẻ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Mệnh giá')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Giá bán')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Tồn kho')
                    ->badge()
                    ->color(fn ($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cards_count')
                    ->label('Số thẻ')
                    ->counts('availableCards')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Loại thẻ')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Không hoạt động'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardDenominations::route('/'),
            'create' => Pages\CreateCardDenomination::route('/create'),
            'edit' => Pages\EditCardDenomination::route('/{record}/edit'),
        ];
    }
}