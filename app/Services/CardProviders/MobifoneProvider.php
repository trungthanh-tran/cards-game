<?php

namespace App\Services\CardProviders;

class MobifoneProvider extends BaseCardProvider
{
    public function getCards(int $denominationId, int $quantity): array
    {
        // Tương tự ViettelProvider
        // Implement theo API của Mobifone
        
        return [
            'success' => true,
            'cards' => [],
            'message' => 'Mobifone provider'
        ];
    }

    public function checkStock(int $denominationId): int
    {
        return 999; // Mock data
    }

    public function testConnection(): bool
    {
        return true;
    }
}