<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardCategoryResource\Pages;
use App\Models\CardCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CardCategoryResource extends Resource
{
    protected static ?string $model = CardCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Loại thẻ';
    protected static ?string $navigationGroup = 'Quản lý thẻ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Tên loại thẻ')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            
            Forms\Components\FileUpload::make('image')
                ->label('Hình ảnh')
                ->image()
                ->directory('card-categories'),
            
            Forms\Components\Textarea::make('description')
                ->label('Mô tả')
                ->rows(3),
            
            Forms\Components\Toggle::make('is_active')
                ->label('Kích hoạt')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                ->label('Hình ảnh')
                ->disk('public') // BẮT BUỘC: Chỉ định disk lưu trữ (thường là 'public')
                ->visibility('public'), // Tùy chọn: Đảm bảo hình ảnh được truy cập công khai,
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),
                Tables\Columns\TextColumn::make('denominations_count')
                    ->label('Số mệnh giá')
                    ->counts('denominations'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
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
            'index' => Pages\ListCardCategories::route('/'),
            'create' => Pages\CreateCardCategory::route('/create'),
            'edit' => Pages\EditCardCategory::route('/{record}/edit'),
        ];
    }
}