<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'system_name', 'value' => 'Pedalya IoT Bicycle Rental'],
            ['key' => 'system_version', 'value' => '2.0.0'],
            ['key' => 'rental_rate_per_hour', 'value' => '15.00'],
            ['key' => 'max_rental_duration_hours', 'value' => '12'],
            ['key' => 'deposit_amount', 'value' => '100.00'],
            ['key' => 'late_fee_per_hour', 'value' => '25.00'],
            ['key' => 'geofence_radius', 'value' => '500'],
            ['key' => 'geofence_center_lat', 'value' => '14.5995'],
            ['key' => 'geofence_center_lng', 'value' => '120.9842'],
            ['key' => 'low_battery_threshold', 'value' => '20'],
            ['key' => 'accident_sensitivity', 'value' => '2.5'],
            ['key' => 'auto_lock_on_theft', 'value' => 'true'],
            ['key' => 'maintenance_interval_days', 'value' => '30'],
            ['key' => 'device_heartbeat_interval', 'value' => '30'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
