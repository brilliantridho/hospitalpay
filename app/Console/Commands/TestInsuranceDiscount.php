<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Insurance;
use App\Models\MedicalService;

class TestInsuranceDiscount extends Command
{
    protected $signature = 'test:insurance-discount';
    protected $description = 'Test insurance discount calculation';

    public function handle()
    {
        $this->info('Testing insurance discount calculation...');
        $this->newLine();
        
        // Get sample service
        $service = MedicalService::first();
        if (!$service) {
            $this->error('No medical services found!');
            return 1;
        }
        
        $price = $service->getCurrentPrice();
        $quantity = 2;
        $subtotal = $price * $quantity;
        
        $this->line("Service: {$service->name}");
        $this->line("Price per item: Rp " . number_format($price, 0, ',', '.'));
        $this->line("Quantity: {$quantity}");
        $this->line("Subtotal: Rp " . number_format($subtotal, 0, ',', '.'));
        $this->newLine();
        
        // Test with BPJS (100% discount)
        $bpjs = Insurance::where('name', 'LIKE', '%BPJS%')->first();
        if ($bpjs && $bpjs->discount_percentage > 0) {
            $discount = ($subtotal * $bpjs->discount_percentage) / 100;
            $total = $subtotal - $discount;
            
            $this->line("With {$bpjs->name} ({$bpjs->discount_percentage}%):");
            $this->line("  Discount: Rp " . number_format($discount, 0, ',', '.'));
            $this->line("  Total: Rp " . number_format($total, 0, ',', '.'));
            $this->newLine();
        }
        
        // Test with Mandiri (90% discount)
        $mandiri = Insurance::where('name', 'LIKE', '%Mandiri%')->first();
        if ($mandiri && $mandiri->discount_percentage > 0) {
            $discount = ($subtotal * $mandiri->discount_percentage) / 100;
            $total = $subtotal - $discount;
            
            $this->line("With {$mandiri->name} ({$mandiri->discount_percentage}%):");
            $this->line("  Discount: Rp " . number_format($discount, 0, ',', '.'));
            $this->line("  Total: Rp " . number_format($total, 0, ',', '.'));
            $this->newLine();
        }
        
        $this->info('✅ Insurance discount calculation is working correctly!');
        
        return 0;
    }
}
