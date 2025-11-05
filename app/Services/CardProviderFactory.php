<?php

namespace App\Services;

use App\Contracts\CardProviderInterface;
use App\Models\CardProvider;
use App\Services\CardProviders\ViettelProvider;
use App\Services\CardProviders\MobifoneProvider;

class CardProviderFactory
{
    public static function make(string $providerCode): ?CardProviderInterface
    {
        $provider = CardProvider::where('code', $providerCode)
            ->where('is_active', true)
            ->first();

        if (!$provider) {
            return null;
        }

        return match($providerCode) {
            'viettel_api' => new ViettelProvider($provider),
            'mobifone_api' => new MobifoneProvider($provider),
            default => null,
        };
    }
}