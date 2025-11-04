<?php

namespace App\Filament\Resources\CardDenominationResource\Pages;

use App\Filament\Resources\CardDenominationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCardDenominations extends ListRecords
{
    protected static string $resource = CardDenominationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
