<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use App\Models\Card;
use Illuminate\Support\Facades\DB;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_import')
                ->label('Import nhiều thẻ')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
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
                        ->preload(),
                    
                    Forms\Components\DatePicker::make('expiry_date')
                        ->label('Ngày hết hạn')
                        ->required()
                        ->default(now()->addYear())
                        ->minDate(now())
                        ->native(false),
                    
                    Forms\Components\Textarea::make('cards_data')
                        ->label('Dữ liệu thẻ')
                        ->required()
                        ->rows(10)
                        ->placeholder("Nhập mỗi thẻ trên 1 dòng theo định dạng:\nSERIAL,CODE\n\nVí dụ:\nABC123456789,XYZ987654321\nDEF123456789,UVW987654321")
                        ->helperText('Mỗi dòng: SERIAL,CODE (cách nhau bởi dấu phẩy)'),
                ])
                ->action(function (array $data) {
                    $lines = explode("\n", trim($data['cards_data']));
                    $imported = 0;
                    $errors = [];

                    DB::beginTransaction();
                    try {
                        foreach ($lines as $index => $line) {
                            $line = trim($line);
                            if (empty($line)) continue;

                            $parts = array_map('trim', explode(',', $line));
                            if (count($parts) !== 2) {
                                $errors[] = "Dòng " . ($index + 1) . ": Sai định dạng";
                                continue;
                            }

                            [$serial, $code] = $parts;

                            // Kiểm tra serial đã tồn tại
                            if (Card::where('serial', $serial)->exists()) {
                                $errors[] = "Dòng " . ($index + 1) . ": Serial '$serial' đã tồn tại";
                                continue;
                            }

                            Card::create([
                                'denomination_id' => $data['denomination_id'],
                                'serial' => $serial,
                                'code' => $code,
                                'expiry_date' => $data['expiry_date'],
                                'status' => 'available',
                            ]);

                            $imported++;
                        }

                        // Cập nhật stock
                        $denomination = \App\Models\CardDenomination::find($data['denomination_id']);
                        if ($denomination) {
                            $denomination->update([
                                'stock' => $denomination->availableCards()->count()
                            ]);
                        }

                        DB::commit();

                        $message = "✅ Import thành công {$imported} thẻ!";
                        if (!empty($errors)) {
                            $message .= "\n⚠️ Có " . count($errors) . " lỗi:\n" . implode("\n", array_slice($errors, 0, 5));
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Import hoàn tất')
                            ->body($message)
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Filament\Notifications\Notification::make()
                            ->title('Lỗi import')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalWidth('2xl'),
            
            Actions\CreateAction::make(),
        ];
    }
}
