<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'insurance_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_transaction',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_transaction' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer'
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function calculateDiscount($amount)
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $amount * ($this->discount_value / 100);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return $discount;
        } else {
            return $this->discount_value;
        }
    }

    public function isValid($transactionAmount = null)
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        // Check minimum transaction
        if ($transactionAmount !== null && $this->min_transaction) {
            if ($transactionAmount < $this->min_transaction) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get voucher validation message
     */
    public function getValidationMessage($transactionAmount = null)
    {
        if (!$this->is_active) {
            return 'Voucher tidak aktif';
        }

        $now = Carbon::now();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return 'Voucher belum berlaku (mulai: ' . $this->valid_from->format('d/m/Y') . ')';
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return 'Voucher sudah kadaluarsa (berlaku sampai: ' . $this->valid_until->format('d/m/Y') . ')';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Voucher sudah mencapai batas penggunaan';
        }

        if ($transactionAmount !== null && $this->min_transaction) {
            if ($transactionAmount < $this->min_transaction) {
                return 'Minimum transaksi Rp ' . number_format($this->min_transaction, 0, ',', '.');
            }
        }

        return 'Voucher valid';
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    /**
     * Get formatted discount text
     */
    public function getDiscountText()
    {
        if ($this->discount_type === 'percentage') {
            $text = $this->discount_value . '%';
            if ($this->max_discount) {
                $text .= ' (max Rp ' . number_format($this->max_discount, 0, ',', '.') . ')';
            }
            return $text;
        } else {
            return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
        }
    }
}
