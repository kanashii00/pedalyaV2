<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Geofence;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;

class SettingController extends Controller
{
    protected array $defaults = [
        'companyName' => 'Pedalya',
        'companyEmail' => 'hello@pedalya.com',
        'companyPhone' => '+63 900 000 0000',
        'companyAddress' => 'Azuela Cove, J.P. Laurel Ave., Lanang, Davao City',
        'rentalRatePerHour' => 15,
        'rentalMaxDurationHours' => 12,
        'depositAmount' => 100,
        'geofenceEnabled' => true,
        'geofenceCenterLat' => 7.0990,
        'geofenceCenterLng' => 125.6470,
        'geofenceRadius' => 400,
        'geofenceWarningThreshold' => 75,
        'deviceTimeoutMinutes' => 5,
        'lowBatteryThreshold' => 20,
        'overdueBuzzerMinutes' => 5,
    ];

    public function index(): Response
    {
        $settings = $this->loadSettings();

        $geofence = $this->geofenceConfig();

        return response()->view('admin.settings', compact('settings', 'geofence'));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'companyName'            => ['sometimes', 'string', 'max:255'],
            'companyEmail'           => ['sometimes', 'email', 'max:255'],
            'companyPhone'           => ['sometimes', 'string', 'max:50'],
            'companyAddress'         => ['sometimes', 'string', 'max:500'],
            'rentalRatePerHour'      => ['sometimes', 'numeric', 'min:0'],
            'rentalMaxDurationHours' => ['sometimes', 'integer', 'min:1'],
            'depositAmount'          => ['sometimes', 'numeric', 'min:0'],
            'geofenceEnabled'        => ['sometimes', 'boolean'],
            'geofenceCenterLat'      => ['sometimes', 'numeric', 'between:-90,90'],
            'geofenceCenterLng'      => ['sometimes', 'numeric', 'between:-180,180'],
            'geofenceRadius'         => ['sometimes', 'numeric', 'min:10', 'max:50000'],
            'geofenceWarningThreshold' => ['sometimes', 'numeric', 'min:1'],
            'deviceTimeoutMinutes'   => ['sometimes', 'integer', 'min:1'],
            'lowBatteryThreshold'    => ['sometimes', 'integer', 'min:0', 'max:100'],
            'overdueBuzzerMinutes'   => ['sometimes', 'integer', 'min:0'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? $value : (string) $value]
            );
        }

        if (array_intersect(['geofenceEnabled', 'geofenceCenterLat', 'geofenceCenterLng', 'geofenceRadius'], array_keys($validated))) {
            $geofence = Geofence::where('isActive', true)->first() ?? new Geofence();

            $geofence->name = 'Azuela Cove Riding Zone';
            $geofence->centerLat = $validated['geofenceCenterLat'] ?? $geofence->centerLat ?? $this->defaults['geofenceCenterLat'];
            $geofence->centerLng = $validated['geofenceCenterLng'] ?? $geofence->centerLng ?? $this->defaults['geofenceCenterLng'];
            $geofence->radius = $validated['geofenceRadius'] ?? $geofence->radius ?? $this->defaults['geofenceRadius'];
            $geofence->alertEnabled = $validated['geofenceEnabled'] ?? $geofence->alertEnabled ?? true;
            $geofence->isActive = true;
            $geofence->save();

            Geofence::where('isActive', true)->where('id', '!=', $geofence->id)->update(['isActive' => false]);
        }

        AuditLog::record('system_settings_updated', auth()->id(), ['keys' => array_keys($validated)]);

        return response()->json(['message' => 'Settings updated successfully.']);
    }

    private function loadSettings(): stdClass
    {
        $stored = SystemSetting::pluck('value', 'key')->all();

        $settings = new stdClass();

        foreach ($this->defaults as $key => $default) {
            $settings->{$key} = $stored[$key] ?? $default;
        }

        return $settings;
    }

    private function geofenceConfig(): array
    {
        $geofence = Geofence::where('isActive', true)->first();

        return $geofence ? [
            'centerLat' => (float) $geofence->centerLat,
            'centerLng' => (float) $geofence->centerLng,
            'radius' => (float) $geofence->radius,
            'alertEnabled' => (bool) $geofence->alertEnabled,
        ] : [
            'centerLat' => (float) config('services.geofence.center_lat', 7.0990),
            'centerLng' => (float) config('services.geofence.center_lng', 125.6470),
            'radius' => (float) config('services.geofence.default_radius', 400),
            'alertEnabled' => true,
        ];
    }
}
