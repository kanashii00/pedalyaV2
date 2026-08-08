<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pedalya.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'verified' => true,
                'email_verified_at' => now(),
                'phoneNumber' => '+63 917 000 0001',
                'address' => 'Pedalya HQ, Manila, Philippines',
            ]
        );
    }
}
