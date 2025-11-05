<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposit($amount, $description = null, $referenceCode = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'reference_code' => $referenceCode,
            'status' => 'completed'
        ]);
    }

    public function withdraw($amount, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Số dư không đủ');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => 'withdraw',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'status' => 'completed'
        ]);
    }
}