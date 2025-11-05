<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'card_id', 'denomination_id', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function denomination()
    {
        return $this->belongsTo(CardDenomination::class);
    }
}