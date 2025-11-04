<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?string $navigationGroup = 'Hệ thống';
    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false; // Không cho phép tạo log thủ công
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('created_at')
                ->label('Thời gian')
                ->content(fn ($record) => $record->created_at->format('d/m/Y H:i:s')),
            
            Forms\Components\Placeholder::make('admin.name')
                ->label('Người thực hiện')
                ->content(fn ($record) => $record->admin?->name ?? 'Hệ thống'),
            
            Forms\Components\Placeholder::make('user.name')
                ->label('User')
                ->content(fn ($record) => $record->user?->name ?? '-'),
            
            Forms\Components\Placeholder::make('action')
                ->label('Hành động')
                ->content(fn ($record) => $record->action),
            
            Forms\Components\Placeholder::make('description')
                ->label('Mô tả')
                ->content(fn ($record) => $record->description),
            
            Forms\Components\KeyValue::make('old_values')
                ->label('Giá trị cũ')
                ->columnSpanFull(),
            
            Forms\Components\KeyValue::make('new_values')
                ->label('Giá trị mới')
                ->columnSpanFull(),
            
            Forms\Components\Placeholder::make('ip_address')
                ->label('IP Address')
                ->content(fn ($record) => $record->ip_address),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('action')
                    ->label('Hành động')
                    ->badge()
                    ->searchable()
                    ->color(fn (string $state): string => match ($state) {
                        'adjust_balance' => 'warning',
                        'create_order' => 'success',
                        'deposit' => 'info',
                        'delete_user' => 'danger',
                        'create_card' => 'success',
                        'delete_card' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Admin')
                    ->default('Hệ thống')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
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
                        'create_card' => 'Tạo thẻ',
                        'delete_card' => 'Xóa thẻ',
                        'delete_user' => 'Xóa user',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('admin_id')
                    ->label('Admin')
                    ->relationship('admin', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Đến ngày'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'], fn ($q) => $q->whereDate('created_at', '<=', $data['to']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto refresh mỗi 30s
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}