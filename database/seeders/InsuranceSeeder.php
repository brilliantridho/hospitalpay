<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Insurance;
use App\Models\Voucher;

class InsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asuransi Reliance
        $reliance = Insurance::create([
            'name' => 'Asuransi Reliance',
            'description' => 'Asuransi kesehatan Reliance'
        ]);

        Voucher::create([
            'insurance_id' => $reliance->id,
            'discount_type' => 'percentage',
            'discount_value' => 5,
            'max_discount' => 35000,
            'valid_from' => '2026-01-01',
            'valid_until' => '2026-01-31',
            'is_active' => true
        ]);

        // Asuransi Allianz
        $allianz = Insurance::create([
            'name' => 'Asuransi Allianz',
            'description' => 'Asuransi kesehatan Allianz'
        ]);

        Voucher::create([
            'insurance_id' => $allianz->id,
            'discount_type' => 'percentage',
            'discount_value' => 1,
            'max_discount' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true
        ]);

        // Asuransi Prudential
        $prudential = Insurance::create([
            'name' => 'Asuransi Prudential',
            'description' => 'Asuransi kesehatan Prudential'
        ]);

        Voucher::create([
            'insurance_id' => $prudential->id,
            'discount_type' => 'fixed',
            'discount_value' => 15000,
            'max_discount' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true
        ]);
    }
}
