<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'kasir'
        ]);

        User::create([
            'name' => 'Marketing 1',
            'email' => 'marketing@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'marketing'
        ]);
    }
}
