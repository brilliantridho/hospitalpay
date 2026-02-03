<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalService;

class MedicalServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Konsultasi Dokter Umum', 'description' => 'Konsultasi dengan dokter umum', 'price' => 150000],
            ['name' => 'Konsultasi Dokter Spesialis', 'description' => 'Konsultasi dengan dokter spesialis', 'price' => 300000],
            ['name' => 'Pemeriksaan Laboratorium Darah Lengkap', 'description' => 'Pemeriksaan lab darah lengkap', 'price' => 200000],
            ['name' => 'Rontgen', 'description' => 'Pemeriksaan rontgen', 'price' => 250000],
            ['name' => 'USG', 'description' => 'Pemeriksaan USG', 'price' => 350000],
            ['name' => 'Rawat Inap Kelas 3', 'description' => 'Rawat inap kelas 3 per hari', 'price' => 500000],
            ['name' => 'Rawat Inap Kelas 2', 'description' => 'Rawat inap kelas 2 per hari', 'price' => 750000],
            ['name' => 'Rawat Inap Kelas 1', 'description' => 'Rawat inap kelas 1 per hari', 'price' => 1000000],
            ['name' => 'Operasi Kecil', 'description' => 'Tindakan operasi kecil', 'price' => 2000000],
            ['name' => 'Operasi Besar', 'description' => 'Tindakan operasi besar', 'price' => 5000000],
        ];

        foreach ($services as $service) {
            MedicalService::create($service);
        }
    }
}
