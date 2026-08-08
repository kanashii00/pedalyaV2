<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RiderSeeder extends Seeder
{
    public function run(): void
    {
        $riders = [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@jmc.edu.ph',
                'phoneNumber' => '+63 917 123 4567',
                'address' => 'Quezon City, Metro Manila',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@jmc.edu.ph',
                'phoneNumber' => '+63 918 234 5678',
                'address' => 'Makati City, Metro Manila',
            ],
            [
                'name' => 'Jose Reyes',
                'email' => 'jose.reyes@jmc.edu.ph',
                'phoneNumber' => '+63 919 345 6789',
                'address' => 'Pasig City, Metro Manila',
            ],
            [
                'name' => 'Ana Garcia',
                'email' => 'ana.garcia@jmc.edu.ph',
                'phoneNumber' => '+63 920 456 7890',
                'address' => 'Taguig City, Metro Manila',
            ],
            [
                'name' => 'Pedro Mendoza',
                'email' => 'pedro.mendoza@jmc.edu.ph',
                'phoneNumber' => '+63 921 567 8901',
                'address' => 'Mandaluyong City, Metro Manila',
            ],
        ];

        foreach ($riders as $rider) {
            User::updateOrCreate(
                ['email' => $rider['email']],
                [
                    'name' => $rider['name'],
                    'password' => Hash::make('password'),
                    'role' => 'rider',
                    'status' => 'active',
                    'verified' => true,
                    'email_verified_at' => now(),
                    'phoneNumber' => $rider['phoneNumber'],
                    'address' => $rider['address'],
                ]
            );
        }
    }
}
