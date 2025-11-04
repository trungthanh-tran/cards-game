<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Thẻ';
    protected static ?string $navigationGroup = 'Quản lý thẻ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('denomination_id')
                        ->label('Loại thẻ & Mệnh giá')
                        ->options(function () {
                            return \App\Models\CardDenomination::with('category')
                                ->get()
                                ->groupBy('category.name')
                                ->map(function ($denominations, $categoryName) {
                                    return $denominations->mapWithKeys(function ($denomination) {
                                        return [
                                            $denomination->id => number_format($denomination->value) . ' VNĐ'
                                        ];
                                    });
                                })
                                ->toArray();
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Chọn loại thẻ và mệnh giá')
                        ->helperText('Thẻ được nhóm theo loại')
                        ->columnSpanFull(),
                    
                    Forms\Components\TextInput::make('serial')
                        ->label('Serial')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Nhập serial thẻ')
                        ->helperText('Mã serial duy nhất của thẻ'),
                    
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Nhập code thẻ')
                        ->helperText('Mã code để nạp thẻ'),
                    
                    Forms\Components\DatePicker::make('expiry_date')
                        ->label('Ngày hết hạn')
                        ->required()
                        ->minDate(now())
                        ->default(now()->addYear())
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->helperText('Ngày thẻ hết hạn sử dụng'),
                    
                    Forms\Components\Select::make('status')
                        ->label('Trạng thái')
                        ->options([
                            'available' => 'Có sẵn',
                            'sold' => 'Đã bán',
                            'expired' => 'Hết hạn',
                        ])
                        ->default('available')
                        ->required()
                        ->native(false)
                        ->helperText('Trạng thái hiện tại của thẻ'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('denomination.category.name')
                    ->label('Loại thẻ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('denomination.value')
                    ->label('Mệnh giá')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('serial')
                    ->label('Serial')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Serial đã copy!')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-m-clipboard')
                    ->iconPosition('after'),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code đã copy!')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-m-clipboard')
                    ->iconPosition('after'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Hết hạn')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date < now() ? 'danger' : 'success'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'available',
                        'danger' => 'sold',
                        'warning' => 'expired',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'available',
                        'heroicon-o-x-circle' => 'sold',
                        'heroicon-o-exclamation-circle' => 'expired',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'available' => 'Có sẵn',
                        'sold' => 'Đã bán',
                        'expired' => 'Hết hạn',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('denomination_id')
                    ->label('Loại thẻ & Mệnh giá')
                    ->relationship('denomination', 'value')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        $record->category->name . ' - ' . number_format($record->value) . ' VNĐ'
                    ),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'available' => 'Có sẵn',
                        'sold' => 'Đã bán',
                        'expired' => 'Hết hạn',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('expiry_date')
                    ->label('Sắp hết hạn')
                    ->query(fn ($query) => $query->where('expiry_date', '<=', now()->addDays(30)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_available')
                        ->label('Đánh dấu: Có sẵn')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'available']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_as_expired')
                        ->label('Đánh dấu: Hết hạn')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'expired']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}