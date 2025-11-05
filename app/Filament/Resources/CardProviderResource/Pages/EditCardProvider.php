<?php

namespace App\Filament\Resources\CardProviderResource\Pages;

use App\Filament\Resources\CardProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardProvider extends EditRecord
{
    protected static string $resource = CardProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
