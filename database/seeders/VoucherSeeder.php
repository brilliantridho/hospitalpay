<?php

namespace Database\Seeders;

use App\Models\Voucher;
use App\Models\Insurance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $insurances = Insurance::all();

        // Update existing vouchers with better codes
        $existingVouchers = Voucher::whereNotNull('insurance_id')->get();
        
        foreach ($existingVouchers as $voucher) {
            $insurance = Insurance::find($voucher->insurance_id);
            if ($insurance) {
                $insuranceName = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($insurance->name)), 0, 6));
                $percentage = $voucher->discount_type === 'percentage' ? (int)$voucher->discount_value : '';
                $newCode = $insuranceName . $percentage . date('Y');
                
                // Check if code exists
                if (Voucher::where('code', $newCode)->where('id', '!=', $voucher->id)->exists()) {
                    $newCode .= $voucher->id;
                }
                
                $voucher->update([
                    'code' => $newCode,
                    'description' => "Voucher diskon untuk asuransi {$insurance->name}",
                    'min_transaction' => 100000, // Minimal transaksi 100rb
                    'usage_limit' => 100, // Batas pemakaian 100x
                    'used_count' => 0
                ]);
            }
        }

        // Create general vouchers (tidak terikat asuransi)
        $generalVouchers = [
            [
                'code' => 'NEWYEAR2026',
                'insurance_id' => null,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount' => 500000,
                'description' => 'Diskon Tahun Baru 2026 - 20% maksimal Rp 500.000',
                'min_transaction' => 500000,
                'usage_limit' => 50,
                'valid_from' => Carbon::parse('2026-01-01'),
                'valid_until' => Carbon::parse('2026-01-31'),
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME100',
                'insurance_id' => null,
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'max_discount' => null,
                'description' => 'Diskon selamat datang Rp 100.000',
                'min_transaction' => 300000,
                'usage_limit' => 200,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'RAMADAN50',
                'insurance_id' => null,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount' => 300000,
                'description' => 'Diskon Ramadan 15% maksimal Rp 300.000',
                'min_transaction' => 200000,
                'usage_limit' => 100,
                'valid_from' => Carbon::parse('2026-03-01'),
                'valid_until' => Carbon::parse('2026-04-30'),
                'is_active' => true,
            ],
        ];

        foreach ($generalVouchers as $voucherData) {
            Voucher::firstOrCreate(
                ['code' => $voucherData['code']],
                $voucherData
            );
        }

        $this->command->info('Vouchers seeded successfully!');
    }
}
