<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['denomination_id', 'serial', 'code', 'expiry_date', 'status'];
    protected $casts = ['expiry_date' => 'date'];

    public function denomination()
    {
        return $this->belongsTo(CardDenomination::class, 'denomination_id');
    }

    public function orderItem()
    {
        return $this->hasOne(OrderItem::class, 'card_id');
    }
}