<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Insurance;

class CheckInsuranceData extends Command
{
    protected $signature = 'check:insurance';
    protected $description = 'Check insurance discount data';

    public function handle()
    {
        $this->info('Checking insurance data...');
        $this->newLine();
        
        $insurances = Insurance::all(['id', 'name', 'discount_percentage']);
        
        if ($insurances->isEmpty()) {
            $this->error('No insurance data found!');
            return 1;
        }
        
        foreach ($insurances as $insurance) {
            $this->line(sprintf(
                'ID: %d | %s | Diskon: %s%%',
                $insurance->id,
                $insurance->name,
                $insurance->discount_percentage ?? '0'
            ));
        }
        
        $this->newLine();
        $this->info('Total: ' . $insurances->count() . ' insurance records');
        
        return 0;
    }
}
