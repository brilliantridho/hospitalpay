<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Insurance;

class UpdateInsuranceDiscounts extends Command
{
    protected $signature = 'insurance:update-discounts';
    protected $description = 'Update insurance discount percentages';

    public function handle()
    {
        $this->info('Updating insurance discounts...');
        $this->newLine();
        
        // Data diskon untuk setiap asuransi
        $discounts = [
            'BPJS Kesehatan' => 100,
            'Mandiri Inhealth' => 90,
            'Allianz Indonesia' => 85,
            'Asuransi Allianz' => 80,
            'Prudential' => 75,
            'Asuransi Prudential' => 75,
            'Manulife Indonesia' => 70,
            'BCA Life' => 65,
            'Asuransi Reliance' => 60,
            'Reliance Indonesia' => 60,
        ];
        
        $updated = 0;
        $notFound = 0;
        
        foreach ($discounts as $name => $discount) {
            $insurance = Insurance::where('name', 'LIKE', "%{$name}%")->first();
            
            if ($insurance) {
                $insurance->update(['discount_percentage' => $discount]);
                $this->line("✅ {$insurance->name}: {$discount}%");
                $updated++;
            } else {
                $this->warn("⚠️ Not found: {$name}");
                $notFound++;
            }
        }
        
        $this->newLine();
        $this->info("Updated: {$updated} insurances");
        if ($notFound > 0) {
            $this->warn("Not found: {$notFound} insurances");
        }
        
        return 0;
    }
}
