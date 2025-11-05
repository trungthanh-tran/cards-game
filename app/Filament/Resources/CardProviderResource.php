<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardProviderResource\Pages;
use App\Models\CardProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardProviderResource extends Resource
{
    protected static ?string $model = CardProvider::class;
    protected static ?string $navigationIcon = 'heroicon-o-cloud';
    protected static ?string $navigationLabel = 'API Providers';
    protected static ?string $navigationGroup = 'Cấu hình';
    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin Provider')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Tên nhà cung cấp')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ví dụ: Viettel API'),
                    
                    Forms\Components\TextInput::make('code')
                        ->label('Mã provider')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Ví dụ: viettel_api')
                        ->helperText('Mã duy nhất, không có khoảng trắng'),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Mô tả')
                        ->rows(3),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Section::make('Cấu hình API')
                ->schema([
                    Forms\Components\TextInput::make('api_url')
                        ->label('API URL')
                        ->url()
                        ->placeholder('https://api.example.com')
                        ->helperText('URL gốc của API'),
                    
                    Forms\Components\TextInput::make('api_key')
                        ->label('API Key')
                        ->password()
                        ->revealable()
                        ->placeholder('Nhập API key'),
                    
                    Forms\Components\TextInput::make('api_secret')
                        ->label('API Secret')
                        ->password()
                        ->revealable()
                        ->placeholder('Nhập API secret (nếu có)'),
                    
                    Forms\Components\KeyValue::make('api_config')
                        ->label('Cấu hình bổ sung')
                        ->keyLabel('Tham số')
                        ->valueLabel('Giá trị')
                        ->helperText('Các tham số cấu hình khác'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('api_url')
                    ->label('API URL')
                    ->limit(50)
                    ->copyable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Đang sử dụng')
                    ->counts('categories')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('test_connection')
                        ->label('Test kết nối')
                        ->icon('heroicon-o-signal')
                        ->color('info')
                        ->action(function ($record) {
                            try {
                                $provider = \App\Services\CardProviderFactory::make($record->code);
                                
                                if (!$provider) {
                                    throw new \Exception('Provider class không tồn tại');
                                }

                                $result = $provider->testConnection();
                                
                                \Filament\Notifications\Notification::make()
                                    ->title($result ? 'Kết nối thành công!' : 'Kết nối thất bại!')
                                    ->body($result ? 'API hoạt động bình thường' : 'Không thể kết nối đến API')
                                    ->color($result ? 'success' : 'danger')
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Lỗi')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListCardProviders::route('/'),
            'create' => Pages\CreateCardProvider::route('/create'),
            'edit' => Pages\EditCardProvider::route('/{record}/edit'),
        ];
    }
}