<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ExternalApiService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MedicalService extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'category',
        'price_updated_at',
        'price_source'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_updated_at' => 'datetime'
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get current price for this medical service
     * Priority: Database price (if recently updated) > API (fallback)
     * 
     * @param string|null $date Date in Y-m-d format, defaults to today
     * @return float
     */
    public function getCurrentPrice($date = null)
    {
        // Priority 1: Use database price if available and recently updated (within 24 hours)
        if ($this->price && $this->price > 0) {
            // If price was updated within last 24 hours, use it
            if ($this->price_updated_at && $this->price_updated_at->diffInHours(now()) < 24) {
                return (float) $this->price;
            }
            
            // If no code (can't fetch from API), use database price
            if (!$this->code) {
                return (float) $this->price;
            }
        }
        
        // Priority 2: Try to fetch from API if code exists
        if ($this->code) {
            $date = $date ?? Carbon::today()->format('Y-m-d');
            
            // Try to get from cache first (cache for 1 hour)
            $cacheKey = "medical_service_price_{$this->code}_{$date}";
            
            $apiPrice = Cache::remember($cacheKey, 3600, function () use ($date) {
                try {
                    $apiService = app(ExternalApiService::class);
                    $prices = $apiService->getProcedurePrices($this->code);
                    
                    if ($prices && is_array($prices)) {
                        // Find price that is valid for the given date
                        foreach ($prices as $priceData) {
                            $startDate = $priceData['start_date']['value'] ?? null;
                            $endDate = $priceData['end_date']['value'] ?? null;
                            
                            if ($startDate && $endDate) {
                                if ($date >= $startDate && $date <= $endDate) {
                                    $apiPrice = (float) ($priceData['unit_price'] ?? 0);
                                    
                                    // Update database with new price
                                    $this->update([
                                        'price' => $apiPrice,
                                        'price_updated_at' => now(),
                                        'price_source' => 'api'
                                    ]);
                                    
                                    return $apiPrice;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to get current price for procedure {$this->code}: " . $e->getMessage());
                }
                
                return null;
            });
            
            if ($apiPrice && $apiPrice > 0) {
                return $apiPrice;
            }
        }
        
        // Priority 3: Fallback to database price (even if old)
        if ($this->price && $this->price > 0) {
            return (float) $this->price;
        }
        
        // No price available
        throw new \Exception("Harga tidak tersedia untuk tindakan {$this->name}");
    }

    /**
     * Get all available prices from API
     * 
     * @return array|null
     */
    public function getAllPrices()
    {
        try {
            $apiService = app(ExternalApiService::class);
            return $apiService->getProcedurePrices($this->code);
        } catch (\Exception $e) {
            \Log::warning("Failed to get prices for procedure {$this->code}: " . $e->getMessage());
            return null;
        }
    }
}
