<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    protected $baseUrl;
    protected $email;
    protected $password;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.external_api.base_url');
        $this->email = config('services.external_api.email');
        $this->password = config('services.external_api.password');
    }

    /**
     * Authenticate and get access token
     * 
     * @return array ['success' => bool, 'token' => string|null, 'message' => string]
     */
    public function authenticate()
    {
        // Check if token exists in cache
        $cachedToken = Cache::get('external_api_token');
        if ($cachedToken) {
            $this->token = $cachedToken;
            return [
                'success' => true,
                'token' => $cachedToken,
                'message' => 'Token retrieved from cache'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/auth', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                $expiresIn = $data['expires_in'] ?? 86400; // default 24 hours

                if ($token) {
                    // Cache token for expires_in - 5 minutes as buffer
                    $cacheMinutes = floor(($expiresIn - 300) / 60); // subtract 5 minutes
                    Cache::put('external_api_token', $token, now()->addMinutes($cacheMinutes));
                    $this->token = $token;
                    
                    Log::info('External API authentication successful', [
                        'expires_in' => $expiresIn,
                        'cache_minutes' => $cacheMinutes
                    ]);
                    
                    return [
                        'success' => true,
                        'token' => $token,
                        'message' => 'Autentikasi berhasil'
                    ];
                }
            }

            // Parse error message
            $errorMessage = $this->parseAuthenticationError($response);

            Log::error('External API authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'parsed_message' => $errorMessage
            ]);
            
            return [
                'success' => false,
                'token' => null,
                'message' => $errorMessage
            ];
        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan koneksi: ' . $e->getMessage();
            Log::error('External API authentication error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'token' => null,
                'message' => $errorMessage
            ];
        }
    }

    /**
     * Parse authentication error response
     * 
     * @param \Illuminate\Http\Client\Response $response
     * @return string
     */
    protected function parseAuthenticationError($response)
    {
        $status = $response->status();
        $body = $response->json();

        // Handle 422 Validation Error
        if ($status === 422) {
            $errors = $body['errors'] ?? [];
            $emailError = $errors['email'][0] ?? null;
            $passwordError = $errors['password'][0] ?? null;

            if ($emailError && $passwordError) {
                return 'Email dan password tidak valid. Periksa kembali kredensial Anda.';
            } elseif ($emailError) {
                if (strpos($emailError, 'tidak valid') !== false) {
                    return 'Email tidak terdaftar di sistem. Pastikan email Anda sudah terdaftar.';
                }
                return 'Email tidak valid: ' . $emailError;
            } elseif ($passwordError) {
                if (strpos($passwordError, 'salah') !== false) {
                    return 'Password yang Anda masukkan salah. Periksa kembali password Anda.';
                }
                return 'Password tidak valid: ' . $passwordError;
            }

            // Generic validation error
            return $body['message'] ?? 'Data yang Anda masukkan tidak valid.';
        }

        // Handle 401 Unauthorized
        if ($status === 401) {
            return 'Autentikasi gagal. Email atau password salah.';
        }

        // Handle 500 Server Error
        if ($status >= 500) {
            return 'Server API eksternal sedang bermasalah. Silakan coba lagi nanti.';
        }

        // Handle other errors
        return $body['message'] ?? 'Gagal terhubung ke API eksternal (Status: ' . $status . ')';
    }

    /**
     * Get the current token, authenticate if needed
     * 
     * @return string|null
     */
    protected function getToken()
    {
        if (!$this->token) {
            $result = $this->authenticate();
            return $result['token'] ?? null;
        }
        return $this->token;
    }

    /**
     * Get list of insurances from external API
     * 
     * @return array|null
     */
    public function getInsurances()
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/insurances');

            if ($response->successful()) {
                $data = $response->json();
                // API returns {"insurances": [...]}
                return $data['insurances'] ?? $data;
            }

            // If unauthorized, try to re-authenticate
            if ($response->status() === 401) {
                Cache::forget('external_api_token');
                $this->token = null;
                
                // Retry once with new token
                $authResult = $this->authenticate();
                if ($authResult['success'] && $authResult['token']) {
                    $response = Http::withToken($authResult['token'])
                        ->get($this->baseUrl . '/insurances');
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        return $data['insurances'] ?? $data;
                    }
                }
            }

            Log::error('Failed to get insurances from external API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting insurances from external API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get list of medical procedures (tindakan) from external API
     * 
     * @return array|null
     */
    public function getMedicalServices()
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/procedures');

            if ($response->successful()) {
                $data = $response->json();
                // API returns {"procedures": [...]}
                return $data['procedures'] ?? $data;
            }

            // If unauthorized, try to re-authenticate
            if ($response->status() === 401) {
                Cache::forget('external_api_token');
                $this->token = null;
                
                // Retry once with new token
                $authResult = $this->authenticate();
                if ($authResult['success'] && $authResult['token']) {
                    $response = Http::withToken($authResult['token'])
                        ->get($this->baseUrl . '/procedures');
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        return $data['procedures'] ?? $data;
                    }
                }
            }

            Log::error('Failed to get procedures from external API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting procedures from external API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get prices for a specific medical procedure
     * 
     * @param string $procedureId
     * @return array|null
     */
    public function getProcedurePrices($procedureId)
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/procedures/' . $procedureId . '/prices');

            if ($response->successful()) {
                $data = $response->json();
                // API returns {"prices": [...]}
                return $data['prices'] ?? $data;
            }

            // If unauthorized, try to re-authenticate
            if ($response->status() === 401) {
                Cache::forget('external_api_token');
                $this->token = null;
                
                // Retry once with new token
                $authResult = $this->authenticate();
                if ($authResult['success'] && $authResult['token']) {
                    $response = Http::withToken($authResult['token'])
                        ->get($this->baseUrl . '/procedures/' . $procedureId . '/prices');
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        return $data['prices'] ?? $data;
                    }
                }
            }

            Log::error('Failed to get procedure prices from external API', [
                'procedure_id' => $procedureId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting procedure prices from external API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync all procedure prices to database
     * Update medical_services table with current prices from API
     * 
     * @param string|null $date Date in Y-m-d format, defaults to today
     * @return array ['total' => int, 'updated' => int, 'failed' => int, 'details' => array]
     */
    public function syncAllPrices($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        $medicalServices = \App\Models\MedicalService::whereNotNull('code')->get();
        
        $stats = [
            'total' => $medicalServices->count(),
            'updated' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($medicalServices as $service) {
            try {
                $prices = $this->getProcedurePrices($service->code);
                
                if ($prices && is_array($prices)) {
                    // Find price valid for the given date
                    $currentPrice = null;
                    foreach ($prices as $priceData) {
                        $startDate = $priceData['start_date']['value'] ?? null;
                        $endDate = $priceData['end_date']['value'] ?? null;
                        
                        if ($startDate && $endDate) {
                            if ($date >= $startDate && $date <= $endDate) {
                                $currentPrice = (float) ($priceData['unit_price'] ?? 0);
                                break;
                            }
                        }
                    }
                    
                    if ($currentPrice && $currentPrice > 0) {
                        $service->update([
                            'price' => $currentPrice,
                            'price_updated_at' => now(),
                            'price_source' => 'api'
                        ]);
                        
                        $stats['updated']++;
                        $stats['details'][] = [
                            'service' => $service->name,
                            'code' => $service->code,
                            'price' => $currentPrice,
                            'status' => 'updated'
                        ];
                        
                        Log::info("Updated price for {$service->name}: Rp {$currentPrice}");
                    } else {
                        $stats['failed']++;
                        $stats['details'][] = [
                            'service' => $service->name,
                            'code' => $service->code,
                            'status' => 'no_valid_price',
                            'reason' => 'No price found for date ' . $date
                        ];
                    }
                } else {
                    $stats['failed']++;
                    $stats['details'][] = [
                        'service' => $service->name,
                        'code' => $service->code,
                        'status' => 'api_failed',
                        'reason' => 'Failed to fetch prices from API'
                    ];
                }
            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['details'][] = [
                    'service' => $service->name,
                    'code' => $service->code,
                    'status' => 'error',
                    'reason' => $e->getMessage()
                ];
                Log::error("Failed to sync price for {$service->name}: " . $e->getMessage());
            }
        }

        return $stats;
    }
}
