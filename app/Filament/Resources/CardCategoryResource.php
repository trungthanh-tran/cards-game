<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardCategoryResource\Pages;
use App\Models\CardCategory;
use App\Models\CardProvider;
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
            Forms\Components\Section::make('Thông tin cơ bản')
                ->schema([
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
                ])
                ->columns(2),

            Forms\Components\Section::make('Cấu hình nguồn thẻ')
                ->schema([
                    Forms\Components\Radio::make('provider_type')
                        ->label('Nguồn lấy thẻ')
                        ->options([
                            'stock' => 'Kho hàng (Stock)',
                            'api' => 'API đối tác',
                        ])
                        ->default('stock')
                        ->required()
                        ->reactive()
                        ->descriptions([
                            'stock' => 'Lấy thẻ từ database (nhập thủ công)',
                            'api' => 'Lấy thẻ tự động từ API đối tác',
                        ])
                        ->inline(),
                    
                    Forms\Components\Select::make('api_provider')
                        ->label('Nhà cung cấp API')
                        ->options(function () {
                            return CardProvider::where('is_active', true)
                                ->pluck('name', 'code');
                        })
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => $get('provider_type') === 'api')
                        ->helperText('Chọn nhà cung cấp API để lấy thẻ tự động'),
                    
                    Forms\Components\KeyValue::make('api_config')
                        ->label('Cấu hình API')
                        ->keyLabel('Tham số')
                        ->valueLabel('Giá trị')
                        ->visible(fn (Forms\Get $get) => $get('provider_type') === 'api')
                        ->helperText('Các tham số bổ sung cho API (nếu có)'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Hình ảnh'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('provider_type')
                    ->label('Nguồn')
                    ->colors([
                        'primary' => 'stock',
                        'success' => 'api',
                    ])
                    ->icons([
                        'heroicon-o-archive-box' => 'stock',
                        'heroicon-o-cloud' => 'api',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'stock' ? 'Kho hàng' : 'API'),
                
                Tables\Columns\TextColumn::make('api_provider')
                    ->label('Provider')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('denominations_count')
                    ->label('Số mệnh giá')
                    ->counts('denominations'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Không hoạt động'),
                
                Tables\Filters\SelectFilter::make('provider_type')
                    ->label('Nguồn thẻ')
                    ->options([
                        'stock' => 'Kho hàng',
                        'api' => 'API',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('test_api')
                        ->label('Test API')
                        ->icon('heroicon-o-signal')
                        ->color('info')
                        ->visible(fn ($record) => $record->provider_type === 'api')
                        ->action(function ($record) {
                            $provider = $record->getProvider();
                            
                            if (!$provider) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Lỗi')
                                    ->body('Provider không tồn tại')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $result = $provider->testConnection();
                            
                            \Filament\Notifications\Notification::make()
                                ->title($result ? 'Kết nối thành công!' : 'Kết nối thất bại!')
                                ->body($result ? 'API hoạt động bình thường' : 'Không thể kết nối đến API')
                                ->color($result ? 'success' : 'danger')
                                ->send();
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
            'index' => Pages\ListCardCategories::route('/'),
            'create' => Pages\CreateCardCategory::route('/create'),
            'edit' => Pages\EditCardCategory::route('/{record}/edit'),
        ];
    }
}