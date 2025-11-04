<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCard extends EditRecord
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    // Cập nhật stock sau khi xóa
                    $denomination = $record->denomination;
                    if ($denomination) {
                        $denomination->update([
                            'stock' => $denomination->availableCards()->count()
                        ]);
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Cập nhật stock sau khi edit
        $card = $this->record;
        $denomination = $card->denomination;
        
        if ($denomination) {
            $denomination->update([
                'stock' => $denomination->availableCards()->count()
            ]);
        }
    }
}