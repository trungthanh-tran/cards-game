<?php

namespace App\Contracts;

interface CardProviderInterface
{
    /**
     * Lấy thẻ từ API
     * 
     * @param int $denominationId
     * @param int $quantity
     * @return array ['success' => bool, 'cards' => array, 'message' => string]
     */
    public function getCards(int $denominationId, int $quantity): array;

    /**
     * Kiểm tra tồn kho
     * 
     * @param int $denominationId
     * @return int
     */
    public function checkStock(int $denominationId): int;

    /**
     * Test connection
     * 
     * @return bool
     */
    public function testConnection(): bool;
}