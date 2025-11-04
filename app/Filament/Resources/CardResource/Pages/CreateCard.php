<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCard extends CreateRecord
{
    protected static string $resource = CardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Cập nhật stock sau khi tạo thẻ
        $card = $this->record;
        $denomination = $card->denomination;
        
        if ($denomination) {
            $denomination->update([
                'stock' => $denomination->availableCards()->count()
            ]);
        }
    }
}