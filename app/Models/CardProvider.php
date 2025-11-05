<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardProvider extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'api_url',
        'api_key',
        'api_secret',
        'api_config',
        'is_active',
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(CardCategory::class, 'api_provider', 'code');
    }
}