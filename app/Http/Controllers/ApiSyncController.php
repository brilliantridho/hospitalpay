<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use App\Models\Insurance;
use App\Models\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiSyncController extends Controller
{
    protected $apiService;

    public function __construct(ExternalApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Sync insurances from external API
     */
    public function syncInsurances()
    {
        try {
            $insurances = $this->apiService->getInsurances();

            if (!$insurances) {
                return back()->with('error', 'Gagal mendapatkan data asuransi dari API eksternal');
            }

            $syncedCount = 0;
            $updatedCount = 0;

            DB::beginTransaction();

            try {
                foreach ($insurances as $insuranceData) {
                    // API returns: {"id": "uuid", "name": "Insurance Name"}
                    $existing = Insurance::where('code', $insuranceData['id'])->first();
                    
                    if ($existing) {
                        // Update existing: preserve discount_percentage, only update name & description
                        $existing->update([
                            'name' => $insuranceData['name'],
                            'description' => $insuranceData['description'] ?? null,
                            // discount_percentage NOT updated - preserve manual settings
                        ]);
                        $updatedCount++;
                    } else {
                        // Create new: use default discount from API
                        Insurance::create([
                            'code' => $insuranceData['id'],
                            'name' => $insuranceData['name'],
                            'discount_percentage' => $insuranceData['discount_percentage'] ?? 0,
                            'description' => $insuranceData['description'] ?? null,
                        ]);
                        $syncedCount++;
                    }
                }

                DB::commit();

                Log::info('Insurance sync completed', [
                    'synced' => $syncedCount,
                    'updated' => $updatedCount
                ]);

                return back()->with('success', "Berhasil sinkronisasi asuransi: {$syncedCount} baru, {$updatedCount} diperbarui");
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Insurance sync failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi asuransi: ' . $e->getMessage());
        }
    }

    /**
     * Sync medical services from external API
     */
    public function syncMedicalServices()
    {
        try {
            $medicalServices = $this->apiService->getMedicalServices();

            if (!$medicalServices) {
                return back()->with('error', 'Gagal mendapatkan data tindakan medis dari API eksternal');
            }

            $syncedCount = 0;
            $updatedCount = 0;

            DB::beginTransaction();

            try {
                foreach ($medicalServices as $serviceData) {
                    // API returns: {"id": "uuid", "name": "Procedure Name"}
                    $medicalService = MedicalService::updateOrCreate(
                        ['code' => $serviceData['id']], // Use ID as code
                        [
                            'name' => $serviceData['name'],
                            'price' => 0, // Price perlu diambil dari endpoint /prices
                            'category' => $serviceData['category'] ?? null,
                            'description' => $serviceData['description'] ?? null,
                        ]
                    );

                    if ($medicalService->wasRecentlyCreated) {
                        $syncedCount++;
                    } else {
                        $updatedCount++;
                    }
                }

                DB::commit();

                Log::info('Medical services sync completed', [
                    'synced' => $syncedCount,
                    'updated' => $updatedCount
                ]);

                return back()->with('success', "Berhasil sinkronisasi tindakan medis: {$syncedCount} baru, {$updatedCount} diperbarui");
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Medical services sync failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi tindakan medis: ' . $e->getMessage());
        }
    }

    /**
     * Sync all data (insurances and medical services)
     */
    public function syncAll()
    {
        try {
            // Test authentication first
            $authResult = $this->apiService->authenticate();
            
            if (!$authResult['success']) {
                return back()->with('error', $authResult['message']);
            }

            $results = [
                'insurances' => ['success' => false, 'message' => ''],
                'medical_services' => ['success' => false, 'message' => '']
            ];

            // Sync insurances
            $insurances = $this->apiService->getInsurances();
            if ($insurances) {
                $syncedCount = 0;
                $updatedCount = 0;

                DB::beginTransaction();
                try {
                    foreach ($insurances as $insuranceData) {
                        $existing = Insurance::where('code', $insuranceData['code'] ?? $insuranceData['id'])->first();
                        
                        if ($existing) {
                            // Preserve discount_percentage for existing insurances
                            $existing->update([
                                'name' => $insuranceData['name'],
                            ]);
                            $updatedCount++;
                        } else {
                            // Set default discount for new insurances
                            Insurance::create([
                                'code' => $insuranceData['code'] ?? $insuranceData['id'],
                                'name' => $insuranceData['name'],
                                'discount_percentage' => $insuranceData['discount_percentage'] ?? 0,
                            ]);
                            $syncedCount++;
                        }
                    }
                    DB::commit();
                    $results['insurances'] = [
                        'success' => true,
                        'message' => "{$syncedCount} baru, {$updatedCount} diperbarui"
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['insurances']['message'] = 'Gagal: ' . $e->getMessage();
                }
            } else {
                $results['insurances']['message'] = 'Gagal mendapatkan data dari API';
            }

            // Sync medical services
            $medicalServices = $this->apiService->getMedicalServices();
            if ($medicalServices) {
                $syncedCount = 0;
                $updatedCount = 0;

                DB::beginTransaction();
                try {
                    foreach ($medicalServices as $serviceData) {
                        $medicalService = MedicalService::updateOrCreate(
                            ['code' => $serviceData['code'] ?? $serviceData['id']],
                            [
                                'name' => $serviceData['name'],
                                'price' => $serviceData['price'],
                                'category' => $serviceData['category'] ?? null,
                            ]
                        );

                        if ($medicalService->wasRecentlyCreated) {
                            $syncedCount++;
                        } else {
                            $updatedCount++;
                        }
                    }
                    DB::commit();
                    $results['medical_services'] = [
                        'success' => true,
                        'message' => "{$syncedCount} baru, {$updatedCount} diperbarui"
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['medical_services']['message'] = 'Gagal: ' . $e->getMessage();
                }
            } else {
                $results['medical_services']['message'] = 'Gagal mendapatkan data dari API';
            }

            $message = "Sinkronisasi selesai:\n";
            $message .= "- Asuransi: " . $results['insurances']['message'] . "\n";
            $message .= "- Tindakan Medis: " . $results['medical_services']['message'];

            $hasError = !$results['insurances']['success'] || !$results['medical_services']['success'];

            return back()->with($hasError ? 'warning' : 'success', $message);
        } catch (\Exception $e) {
            Log::error('Full sync failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Test API authentication
     */
    public function testAuth()
    {
        try {
            $authResult = $this->apiService->authenticate();
            
            if ($authResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $authResult['message'],
                    'token_preview' => substr($authResult['token'], 0, 20) . '...'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $authResult['message']
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test getting procedure prices
     */
    public function testProcedurePrices(Request $request)
    {
        try {
            $procedureId = $request->input('procedure_id');
            
            if (!$procedureId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Procedure ID is required'
                ], 400);
            }

            $prices = $this->apiService->getProcedurePrices($procedureId);
            
            if ($prices) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prices retrieved successfully',
                    'data' => $prices
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get procedure prices'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
