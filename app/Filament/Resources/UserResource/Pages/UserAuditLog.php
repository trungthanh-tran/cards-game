<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\AuditLog;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class UserAuditLog extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = UserResource::class;
    protected static string $view = 'filament.resources.user-resource.pages.user-audit-log';
    protected static ?string $title = 'Lịch sử hoạt động';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->where('user_id', $this->record->id)
                    ->orWhere('model_id', $this->record->id)
                    ->orWhere('model_id', $this->record->wallet?->id)
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('action')
                    ->label('Hành động')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'adjust_balance' => 'warning',
                        'create_order' => 'success',
                        'deposit' => 'info',
                        'delete_user' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'adjust_balance' => 'Điều chỉnh số dư',
                        'create_order' => 'Tạo đơn hàng',
                        'deposit' => 'Nạp tiền',
                        'withdraw' => 'Rút tiền',
                        'delete_user' => 'Xóa user',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Người thực hiện')
                    ->default('Hệ thống')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Mô tả')
                    ->wrap()
                    ->limit(100),
                
                Tables\Columns\TextColumn::make('changes')
                    ->label('Thay đổi')
                    ->wrap()
                    ->limit(80)
                    ->color('warning')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Hành động')
                    ->options([
                        'adjust_balance' => 'Điều chỉnh số dư',
                        'create_order' => 'Tạo đơn hàng',
                        'deposit' => 'Nạp tiền',
                        'withdraw' => 'Rút tiền',
                    ]),
                
                Tables\Filters\SelectFilter::make('admin_id')
                    ->label('Người thực hiện')
                    ->relationship('admin', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}