<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_percentage',
        'max_discount_amount',
        'terms',
        'coverage_limit',
        'is_active'
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'coverage_limit' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get effective discount percentage
     * Considers both insurance discount and active vouchers
     */
    public function getEffectiveDiscount()
    {
        // Check for active voucher first
        $activeVoucher = $this->vouchers()
            ->where('is_active', true)
            ->where(function($query) {
                $query->where('valid_from', '<=', now())
                      ->where('valid_until', '>=', now());
            })
            ->orderBy('discount_percentage', 'desc')
            ->first();

        if ($activeVoucher) {
            return max($activeVoucher->discount_percentage, $this->discount_percentage);
        }

        return $this->discount_percentage;
    }

    /**
     * Check if insurance has any discount
     */
    public function hasDiscount()
    {
        return $this->discount_percentage > 0 || $this->vouchers()->where('is_active', true)->exists();
    }
}
