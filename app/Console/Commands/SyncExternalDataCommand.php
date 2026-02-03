<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExternalApiService;
use App\Models\Insurance;
use App\Models\MedicalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncExternalDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external:sync 
                            {--insurances : Only sync insurances}
                            {--procedures : Only sync medical procedures}
                            {--prices : Only sync prices for existing procedures}
                            {--force : Force sync even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data (insurances and medical procedures) from external API RS Delta Surya';

    protected $apiService;

    public function __construct(ExternalApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting external data synchronization...');
        $this->newLine();

        // Test authentication first
        $this->info('🔐 Testing authentication...');
        $authResult = $this->apiService->authenticate();
        
        if (!$authResult['success']) {
            $this->error('❌ Authentication failed: ' . $authResult['message']);
            return Command::FAILURE;
        }
        
        $this->info('✅ Authentication successful!');
        $this->newLine();

        $syncInsurances = !$this->option('procedures') && !$this->option('prices') || $this->option('insurances');
        $syncProcedures = !$this->option('insurances') && !$this->option('prices') || $this->option('procedures');
        $syncPrices = $this->option('prices');

        $results = [
            'insurances' => ['success' => false, 'synced' => 0, 'updated' => 0],
            'procedures' => ['success' => false, 'synced' => 0, 'updated' => 0],
            'prices' => ['success' => false, 'updated' => 0, 'failed' => 0],
        ];

        // Sync Insurances
        if ($syncInsurances) {
            $this->info('📋 Syncing insurances...');
            $results['insurances'] = $this->syncInsurances();
            
            if ($results['insurances']['success']) {
                $this->info("✅ Insurances: {$results['insurances']['synced']} new, {$results['insurances']['updated']} updated");
            } else {
                $this->error('❌ Failed to sync insurances');
            }
            $this->newLine();
        }

        // Sync Procedures
        if ($syncProcedures) {
            $this->info('💉 Syncing medical procedures...');
            $results['procedures'] = $this->syncProcedures();
            
            if ($results['procedures']['success']) {
                $this->info("✅ Procedures: {$results['procedures']['synced']} new, {$results['procedures']['updated']} updated");
            } else {
                $this->error('❌ Failed to sync procedures');
            }
            $this->newLine();
        }

        // Sync Prices
        if ($syncPrices || $syncProcedures) {
            $this->info('💰 Syncing procedure prices to database...');
            $results['prices'] = $this->syncPrices();
            
            if ($results['prices']['success']) {
                $this->info("✅ Prices: {$results['prices']['updated']} updated, {$results['prices']['failed']} failed");
            } else {
                $this->error('❌ Failed to sync prices');
            }
            $this->newLine();
        }

        // Summary
        $this->info('📊 Synchronization Summary:');
        $tableData = [];
        
        if ($syncInsurances) {
            $tableData[] = [
                'Insurances', 
                $results['insurances']['success'] ? '✅ Success' : '❌ Failed',
                $results['insurances']['synced'],
                $results['insurances']['updated']
            ];
        }
        
        if ($syncProcedures) {
            $tableData[] = [
                'Procedures', 
                $results['procedures']['success'] ? '✅ Success' : '❌ Failed',
                $results['procedures']['synced'],
                $results['procedures']['updated']
            ];
        }
        
        if ($syncPrices || $syncProcedures) {
            $tableData[] = [
                'Prices', 
                $results['prices']['success'] ? '✅ Success' : '❌ Failed',
                $results['prices']['updated'],
                $results['prices']['failed'] . ' failed'
            ];
        }
        
        $this->table(
            ['Type', 'Status', 'New/Updated', 'Info'],
            $tableData
        );

        $hasError = !$results['insurances']['success'] || !$results['procedures']['success'] || !$results['prices']['success'];
        
        return $hasError ? Command::FAILURE : Command::SUCCESS;
    }

    protected function syncInsurances()
    {
        try {
            $insurances = $this->apiService->getInsurances();

            if (!$insurances) {
                return ['success' => false, 'synced' => 0, 'updated' => 0];
            }

            $syncedCount = 0;
            $updatedCount = 0;

            DB::beginTransaction();

            try {
                foreach ($insurances as $insuranceData) {
                    $insurance = Insurance::updateOrCreate(
                        ['code' => $insuranceData['id']],
                        [
                            'name' => $insuranceData['name'],
                            'discount_percentage' => $insuranceData['discount_percentage'] ?? 0,
                            'description' => $insuranceData['description'] ?? null,
                        ]
                    );

                    if ($insurance->wasRecentlyCreated) {
                        $syncedCount++;
                    } else {
                        $updatedCount++;
                    }
                }

                DB::commit();

                Log::info('Insurance sync completed via command', [
                    'synced' => $syncedCount,
                    'updated' => $updatedCount
                ]);

                return ['success' => true, 'synced' => $syncedCount, 'updated' => $updatedCount];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Insurance sync failed via command: ' . $e->getMessage());
            return ['success' => false, 'synced' => 0, 'updated' => 0];
        }
    }

    protected function syncProcedures()
    {
        try {
            $procedures = $this->apiService->getMedicalServices();

            if (!$procedures) {
                return ['success' => false, 'synced' => 0, 'updated' => 0];
            }

            $syncedCount = 0;
            $updatedCount = 0;

            DB::beginTransaction();

            try {
                foreach ($procedures as $procedureData) {
                    $procedure = MedicalService::updateOrCreate(
                        ['code' => $procedureData['id']],
                        [
                            'name' => $procedureData['name'],
                            'price' => 0, // Price will be fetched from API when needed
                            'category' => $procedureData['category'] ?? null,
                            'description' => $procedureData['description'] ?? null,
                        ]
                    );

                    if ($procedure->wasRecentlyCreated) {
                        $syncedCount++;
                    } else {
                        $updatedCount++;
                    }
                }

                DB::commit();

                Log::info('Procedures sync completed via command', [
                    'synced' => $syncedCount,
                    'updated' => $updatedCount
                ]);

                return ['success' => true, 'synced' => $syncedCount, 'updated' => $updatedCount];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Procedures sync failed via command: ' . $e->getMessage());
            return ['success' => false, 'synced' => 0, 'updated' => 0];
        }
    }

    protected function syncPrices()
    {
        try {
            $this->info('Fetching prices from API for all procedures...');
            $bar = $this->output->createProgressBar(MedicalService::whereNotNull('code')->count());
            
            $stats = $this->apiService->syncAllPrices();
            
            $bar->finish();
            $this->newLine(2);
            
            if ($stats['updated'] > 0) {
                $this->info("Updated {$stats['updated']} prices successfully");
            }
            
            if ($stats['failed'] > 0) {
                $this->warn("Failed to update {$stats['failed']} prices");
                
                // Show first 5 failures
                $failures = array_filter($stats['details'], fn($d) => $d['status'] !== 'updated');
                $showFailures = array_slice($failures, 0, 5);
                
                foreach ($showFailures as $failure) {
                    $this->warn("  - {$failure['service']} ({$failure['code']}): {$failure['reason']}");
                }
                
                if (count($failures) > 5) {
                    $this->warn("  ... and " . (count($failures) - 5) . " more");
                }
            }

            Log::info('Prices sync completed via command', [
                'updated' => $stats['updated'],
                'failed' => $stats['failed']
            ]);

            return [
                'success' => true,
                'updated' => $stats['updated'],
                'failed' => $stats['failed']
            ];
        } catch (\Exception $e) {
            Log::error('Prices sync failed via command: ' . $e->getMessage());
            return ['success' => false, 'updated' => 0, 'failed' => 0];
        }
    }
}
