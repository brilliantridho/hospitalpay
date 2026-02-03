<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'medical_service_id',
        'quantity',
        'price',
        'discount_per_item',
        'subtotal'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_per_item' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }
}
