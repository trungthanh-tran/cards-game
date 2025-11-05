<?php

namespace App\Services\CardProviders;

use App\Models\Card;
use App\Models\CardDenomination;

class ViettelProvider extends BaseCardProvider
{
    public function getCards(int $denominationId, int $quantity): array
    {
        $denomination = CardDenomination::find($denominationId);
        
        if (!$denomination) {
            return ['success' => false, 'message' => 'Mệnh giá không tồn tại'];
        }

        // Call API của Viettel
        $response = $this->makeRequest('POST', '/api/buy-card', [
            'card_type' => 'viettel',
            'amount' => $denomination->value,
            'quantity' => $quantity,
            'partner_code' => $this->config['partner_code'] ?? '',
        ]);

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'Lỗi API: ' . $response['message']
            ];
        }

        // Parse response và tạo cards
        $cards = [];
        $apiCards = $response['data']['cards'] ?? [];

        foreach ($apiCards as $apiCard) {
            $card = Card::create([
                'denomination_id' => $denominationId,
                'serial' => $apiCard['serial'],
                'code' => $apiCard['code'],
                'expiry_date' => $apiCard['expiry_date'] ?? now()->addYear(),
                'status' => 'available',
            ]);

            $cards[] = $card;
        }

        return [
            'success' => true,
            'cards' => $cards,
            'message' => 'Lấy thẻ thành công'
        ];
    }

    public function checkStock(int $denominationId): int
    {
        $denomination = CardDenomination::find($denominationId);
        
        if (!$denomination) {
            return 0;
        }

        // Call API kiểm tra tồn kho
        $response = $this->makeRequest('GET', '/api/check-stock', [
            'card_type' => 'viettel',
            'amount' => $denomination->value,
        ]);

        if ($response['success']) {
            return $response['data']['stock'] ?? 0;
        }

        return 0;
    }

    public function testConnection(): bool
    {
        $response = $this->makeRequest('GET', '/api/ping');
        return $response['success'];
    }
}