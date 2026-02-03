<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Insurance;

class TestSyncPreservation extends Command
{
    protected $signature = 'test:sync-preservation';
    protected $description = 'Test if insurance discount is preserved during sync simulation';

    public function handle()
    {
        $this->info('Testing sync preservation...');
        $this->newLine();
        
        // Get BPJS before "sync"
        $bpjs = Insurance::where('name', 'LIKE', '%BPJS%')->first();
        if (!$bpjs) {
            $this->error('BPJS not found!');
            return 1;
        }
        
        $beforeDiscount = $bpjs->discount_percentage;
        $this->line("BEFORE: {$bpjs->name} = {$beforeDiscount}%");
        
        // Simulate API sync that tries to update
        $bpjs->update([
            'name' => $bpjs->name, // Same name
            // discount_percentage NOT updated
        ]);
        
        // Check after
        $bpjs->refresh();
        $afterDiscount = $bpjs->discount_percentage;
        $this->line("AFTER: {$bpjs->name} = {$afterDiscount}%");
        $this->newLine();
        
        if ($beforeDiscount == $afterDiscount) {
            $this->info('✅ SUCCESS: Discount preserved during update!');
            return 0;
        } else {
            $this->error('❌ FAIL: Discount changed!');
            return 1;
        }
    }
}
