<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/CardDenomination.php
class CardDenomination extends Model
{
    protected $fillable = ['category_id', 'value', 'price', 'stock', 'is_active'];

    public function category()
    {
        return $this->belongsTo(CardCategory::class, 'category_id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'denomination_id');
    }

    public function availableCards()
    {
        return $this->hasMany(Card::class, 'denomination_id')
            ->where('status', 'available')
            ->where('expiry_date', '>', now());
    }
}