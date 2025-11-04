<?php

namespace App\Filament\Resources\CardCategoryResource\Pages;

use App\Filament\Resources\CardCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardCategory extends EditRecord
{
    protected static string $resource = CardCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
