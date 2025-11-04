<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Người dùng';
    protected static ?string $navigationGroup = 'Quản lý người dùng';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin cơ bản')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Họ tên')
                        ->required()
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('phone')
                        ->label('Số điện thoại')
                        ->tel()
                        ->maxLength(20),
                    
                    Forms\Components\TextInput::make('password')
                        ->label('Mật khẩu')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->maxLength(255),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->default(true)
                        ->helperText('Tắt để khóa tài khoản người dùng'),
                ])
                ->columns(2),
            
            Forms\Components\Section::make('Thông tin ví')
                ->schema([
                    Forms\Components\Placeholder::make('wallet_balance')
                        ->label('Số dư hiện tại')
                        ->content(fn (?User $record) => $record?->wallet 
                            ? number_format($record->wallet->balance) . ' VNĐ'
                            : '0 VNĐ'),
                    
                    Forms\Components\Placeholder::make('total_spent')
                        ->label('Tổng chi tiêu')
                        ->content(fn (?User $record) => number_format($record?->total_spent ?? 0) . ' VNĐ'),
                    
                    Forms\Components\Placeholder::make('orders_count')
                        ->label('Số đơn hàng')
                        ->content(fn (?User $record) => $record?->orders()->count() ?? 0),
                ])
                ->columns(3)
                ->visible(fn (string $context) => $context === 'edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('wallet.balance')
                    ->label('Số dư')
                    ->money('VND')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Đơn hàng')
                    ->counts('orders')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày đăng ký')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã khóa'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('adjust_balance')
                        ->label('Điều chỉnh số dư')
                        ->icon('heroicon-o-banknotes')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('type')
                                ->label('Loại giao dịch')
                                ->options([
                                    'add' => 'Cộng tiền',
                                    'subtract' => 'Trừ tiền',
                                    'set' => 'Đặt số dư mới',
                                ])
                                ->required()
                                ->reactive()
                                ->default('add'),
                            
                            Forms\Components\TextInput::make('amount')
                                ->label(fn (Forms\Get $get) => match($get('type')) {
                                    'set' => 'Số dư mới',
                                    default => 'Số tiền',
                                })
                                ->numeric()
                                ->required()
                                ->prefix('VNĐ')
                                ->minValue(0)
                                ->helperText(fn (Forms\Get $get, $record) => 
                                    'Số dư hiện tại: ' . number_format($record->wallet->balance) . ' VNĐ'
                                ),
                            
                            Forms\Components\Textarea::make('reason')
                                ->label('Lý do')
                                ->required()
                                ->rows(3)
                                ->placeholder('Nhập lý do điều chỉnh số dư...'),
                        ])
                        ->action(function (User $record, array $data) {
                            $wallet = $record->wallet;
                            $oldBalance = $wallet->balance;
                            
                            $newBalance = match($data['type']) {
                                'add' => $oldBalance + $data['amount'],
                                'subtract' => max(0, $oldBalance - $data['amount']),
                                'set' => $data['amount'],
                            };

                            $wallet->update(['balance' => $newBalance]);

                            // Tạo transaction log
                            $wallet->transactions()->create([
                                'type' => 'deposit',
                                'amount' => abs($newBalance - $oldBalance),
                                'balance_before' => $oldBalance,
                                'balance_after' => $newBalance,
                                'description' => "Điều chỉnh bởi admin: {$data['reason']}",
                                'reference_code' => 'ADMIN_' . strtoupper(uniqid()),
                                'status' => 'completed',
                            ]);

                            // Tạo audit log
                            AuditLog::createLog(
                                action: 'adjust_balance',
                                description: "Điều chỉnh số dư từ " . number_format($oldBalance) . " VNĐ thành " . number_format($newBalance) . " VNĐ. Lý do: {$data['reason']}",
                                user: $record,
                                admin: auth()->user(),
                                model: $wallet,
                                oldValues: ['balance' => $oldBalance],
                                newValues: ['balance' => $newBalance]
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('Đã cập nhật số dư')
                                ->body("Số dư mới: " . number_format($newBalance) . " VNĐ")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('view_audit')
                        ->label('Xem lịch sử')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn (User $record) => UserResource::getUrl('audit', ['record' => $record])),
                    
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\DeleteAction::make()
                        ->before(function (User $record) {
                            AuditLog::createLog(
                                action: 'delete_user',
                                description: "Xóa người dùng: {$record->name} ({$record->email})",
                                user: $record,
                                admin: auth()->user()
                            );
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'audit' => Pages\UserAuditLog::route('/{record}/audit'),
        ];
    }
}