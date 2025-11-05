<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Đơn hàng';
    protected static ?string $navigationGroup = 'Bán hàng';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Khách hàng')
                ->relationship('user', 'name')
                ->required()
                ->searchable(),
            
            Forms\Components\TextInput::make('order_number')
                ->label('Mã đơn hàng')
                ->disabled(),
            
            Forms\Components\TextInput::make('total_amount')
                ->label('Tổng tiền')
                ->numeric()
                ->prefix('VNĐ')
                ->disabled(),
            
            Forms\Components\Select::make('payment_method')
                ->label('Phương thức thanh toán')
                ->options([
                    'wallet' => 'Ví điện tử',
                    'qr_code' => 'QR Code',
                ])
                ->required(),
            
            Forms\Components\Select::make('status')
                ->label('Trạng thái')
                ->options([
                    'pending' => 'Chờ xử lý',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Mã đơn')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Thanh toán')
                    ->formatStateUsing(fn ($state) => $state === 'wallet' ? 'Ví' : 'QR Code'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Chờ xử lý',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Số thẻ')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày đặt')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            //'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}