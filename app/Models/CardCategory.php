<?php
// app/Models/CardCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardCategory extends Model
{
    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'image', 
        'is_active',
        'provider_type',
        'api_provider',
        'api_config'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_config' => 'array',
    ];

    public function denominations()
    {
        return $this->hasMany(CardDenomination::class, 'category_id');
    }

    /**
     * Check if category uses API provider
     */
    public function isApiProvider(): bool
    {
        return $this->provider_type === 'api';
    }

    /**
     * Check if category uses stock
     */
    public function isStockProvider(): bool
    {
        return $this->provider_type === 'stock';
    }

    /**
     * Get provider instance
     */
    public function getProvider()
    {
        if ($this->isApiProvider() && $this->api_provider) {
            return \App\Services\CardProviderFactory::make($this->api_provider);
        }
        return null;
    }
}