<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'patient_name',
        'insurance_id',
        'voucher_id',
        'user_id',
        'subtotal',
        'discount_amount',
        'total',
        'payment_status',
        'paid_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_code) {
                $transaction->transaction_code = 'TRX' . date('Ymd') . str_pad(static::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
