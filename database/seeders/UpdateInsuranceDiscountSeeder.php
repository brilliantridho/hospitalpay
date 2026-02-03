<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insurance;
use Illuminate\Support\Facades\DB;

class UpdateInsuranceDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insurances = [
            [
                'name' => 'BPJS Kesehatan',
                'discount_percentage' => 100.00,
                'terms' => "• Pasien harus membawa kartu BPJS yang masih aktif\n• Rujukan dari Faskes I wajib untuk layanan spesialis\n• Tanggungan penuh untuk layanan sesuai kelas kartu\n• Tidak berlaku untuk layanan kosmetik dan VIP",
                'coverage_limit' => null, // Unlimited untuk layanan yang ditanggung
                'is_active' => true
            ],
            [
                'name' => 'AdMedika',
                'discount_percentage' => 80.00,
                'terms' => "• Kartu peserta harus aktif dan terdaftar\n• Konfirmasi approval dari pihak asuransi untuk tindakan > Rp 5 juta\n• Tanggungan 80% dari biaya medis\n• 20% ditanggung pasien (Co-insurance)\n• Rawat inap maksimal kamar kelas 1",
                'coverage_limit' => 100000000.00, // 100 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Allianz Indonesia',
                'discount_percentage' => 85.00,
                'terms' => "• Pre-authorization diperlukan untuk rawat inap\n• Tanggungan hingga 85% biaya medis\n• Cashless untuk rumah sakit rekanan\n• Exclude penyakit bawaan yang tidak dilaporkan\n• Rawat inap kelas VIP/Executive",
                'coverage_limit' => 150000000.00, // 150 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Prudential',
                'discount_percentage' => 75.00,
                'terms' => "• Verifikasi eligibilitas sebelum tindakan\n• Tanggungan 75% untuk layanan medis\n• Co-payment 25% ditanggung peserta\n• Limit harian rawat inap Rp 1.5 juta\n• Berlaku untuk kamar kelas 1",
                'coverage_limit' => 80000000.00, // 80 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Mandiri Inhealth',
                'discount_percentage' => 70.00,
                'terms' => "• Kartu harus aktif minimal 30 hari\n• Tanggungan 70% biaya medis yang disetujui\n• Excess 30% dibayar pasien\n• Pre-existing condition exclude 12 bulan pertama\n• Kamar kelas 1 atau setara",
                'coverage_limit' => 75000000.00, // 75 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'BCA Life',
                'discount_percentage' => 65.00,
                'terms' => "• Approval diperlukan untuk tindakan operasi\n• Tanggungan 65% dari total biaya\n• Deductible Rp 500.000 per klaim\n• Waiting period 3 bulan untuk penyakit tertentu\n• Maksimal kamar kelas 1",
                'coverage_limit' => 60000000.00, // 60 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Manulife Indonesia',
                'discount_percentage' => 80.00,
                'terms' => "• Cashless dengan pre-authorization\n• Tanggungan 80% sesuai benefit plan\n• Co-insurance 20%\n• Limit tahunan sesuai polis\n• Kamar kelas VIP tersedia",
                'coverage_limit' => 120000000.00, // 120 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Reliance Indonesia',
                'discount_percentage' => 60.00,
                'terms' => "• Konfirmasi benefit sebelum admisi\n• Tanggungan 60% biaya medis\n• 40% co-payment\n• Tidak cover pre-existing condition tahun pertama\n• Kamar standar kelas 1",
                'coverage_limit' => 50000000.00, // 50 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Sinarmas MSIG',
                'discount_percentage' => 70.00,
                'terms' => "• Letter of Guarantee (LoG) diperlukan\n• Tanggungan 70% biaya yang disetujui\n• Inner limit per item layanan berlaku\n• Waiting period standard 30 hari\n• Kamar kelas 1 atau 2 sesuai plan",
                'coverage_limit' => 85000000.00, // 85 juta per tahun
                'is_active' => true
            ],
            [
                'name' => 'Umum / Biaya Pribadi',
                'discount_percentage' => 0.00,
                'terms' => "• Pembayaran tunai atau transfer bank\n• Tidak ada tanggungan asuransi\n• Full payment oleh pasien\n• Diskon khusus untuk pembayaran cash di muka\n• Cicilan tersedia untuk tindakan > Rp 10 juta",
                'coverage_limit' => null,
                'is_active' => true
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($insurances as $insuranceData) {
                // Try to find by name
                $insurance = Insurance::where('name', 'LIKE', '%' . $insuranceData['name'] . '%')->first();
                
                if ($insurance) {
                    // Update existing
                    $insurance->update([
                        'discount_percentage' => $insuranceData['discount_percentage'],
                        'terms' => $insuranceData['terms'],
                        'coverage_limit' => $insuranceData['coverage_limit'],
                        'is_active' => $insuranceData['is_active']
                    ]);
                    echo "✅ Updated: {$insuranceData['name']} - {$insuranceData['discount_percentage']}%\n";
                } else {
                    // Create new
                    Insurance::create([
                        'name' => $insuranceData['name'],
                        'description' => 'Asuransi kesehatan ' . $insuranceData['name'],
                        'discount_percentage' => $insuranceData['discount_percentage'],
                        'terms' => $insuranceData['terms'],
                        'coverage_limit' => $insuranceData['coverage_limit'],
                        'is_active' => $insuranceData['is_active']
                    ]);
                    echo "✅ Created: {$insuranceData['name']} - {$insuranceData['discount_percentage']}%\n";
                }
            }

            DB::commit();
            echo "\n✅ Successfully updated all insurances with discounts and terms!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "\n❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
