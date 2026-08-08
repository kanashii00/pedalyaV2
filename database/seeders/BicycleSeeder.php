<?php

namespace Database\Seeders;

use App\Models\Bicycle;
use Illuminate\Database\Seeder;

class BicycleSeeder extends Seeder
{
    public function run(): void
    {
        $bicycles = [
            ['name' => 'Urban Cruiser 001', 'serialNumber' => 'PDY-001', 'model' => 'Urban Cruiser', 'status' => 'available', 'batteryLevel' => 100, 'hourlyRate' => 15.00, 'currentLat' => 14.5995, 'currentLng' => 120.9842],
            ['name' => 'Urban Cruiser 002', 'serialNumber' => 'PDY-002', 'model' => 'Urban Cruiser', 'status' => 'available', 'batteryLevel' => 85, 'hourlyRate' => 15.00, 'currentLat' => 14.6005, 'currentLng' => 120.9852],
            ['name' => 'Mountain Explorer 001', 'serialNumber' => 'PDY-003', 'model' => 'Mountain Explorer', 'status' => 'available', 'batteryLevel' => 72, 'hourlyRate' => 20.00, 'currentLat' => 14.5985, 'currentLng' => 120.9832],
            ['name' => 'City Rider 001', 'serialNumber' => 'PDY-004', 'model' => 'City Rider', 'status' => 'maintenance', 'batteryLevel' => 30, 'hourlyRate' => 15.00, 'currentLat' => 14.5975, 'currentLng' => 120.9822],
            ['name' => 'City Rider 002', 'serialNumber' => 'PDY-005', 'model' => 'City Rider', 'status' => 'available', 'batteryLevel' => 95, 'hourlyRate' => 15.00, 'currentLat' => 14.6015, 'currentLng' => 120.9862],
            ['name' => 'Urban Cruiser 003', 'serialNumber' => 'PDY-006', 'model' => 'Urban Cruiser', 'status' => 'available', 'batteryLevel' => 60, 'hourlyRate' => 15.00, 'currentLat' => 14.6025, 'currentLng' => 120.9872],
            ['name' => 'Mountain Explorer 002', 'serialNumber' => 'PDY-007', 'model' => 'Mountain Explorer', 'status' => 'available', 'batteryLevel' => 88, 'hourlyRate' => 20.00, 'currentLat' => 14.5965, 'currentLng' => 120.9812],
            ['name' => 'City Rider 003', 'serialNumber' => 'PDY-008', 'model' => 'City Rider', 'status' => 'available', 'batteryLevel' => 45, 'hourlyRate' => 15.00, 'currentLat' => 14.5955, 'currentLng' => 120.9802],
        ];

        foreach ($bicycles as $bike) {
            Bicycle::updateOrCreate(
                ['serialNumber' => $bike['serialNumber']],
                $bike
            );
        }
    }
}
