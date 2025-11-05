<?php

namespace App\Services\CardProviders;

use App\Contracts\CardProviderInterface;
use App\Models\CardProvider;

abstract class BaseCardProvider implements CardProviderInterface
{
    protected CardProvider $provider;
    protected array $config;

    public function __construct(CardProvider $provider)
    {
        $this->provider = $provider;
        $this->config = $provider->api_config ?? [];
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->provider->api_url,
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->provider->api_key,
                ]
            ]);

            $response = $client->request($method, $endpoint, [
                'json' => $data
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true),
                'status_code' => $response->getStatusCode()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => $e->getCode()
            ];
        }
    }
}
