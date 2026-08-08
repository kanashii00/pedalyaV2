<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'geofence_radius'         => 'nullable|numeric|min:0',
            'geofence_alert_enabled'  => 'nullable|boolean',
            'accident_sensitivity'    => 'nullable|numeric|min:0|max:100',
            'battery_alert_threshold' => 'nullable|numeric|between:0,100',
            'max_rental_hours'        => 'nullable|numeric|min:1',
            'base_fare'               => 'nullable|numeric|min:0',
            'per_minute_rate'         => 'nullable|numeric|min:0',
            'deposit_amount'          => 'nullable|numeric|min:0',
            'parking_fee'             => 'nullable|numeric|min:0',
            'late_fee_per_hour'       => 'nullable|numeric|min:0',
            'heartbeat_interval'      => 'nullable|numeric|min:1',
            'gps_update_interval'     => 'nullable|numeric|min:1',
            'maintenance_interval_days' => 'nullable|numeric|min:1',
            'low_battery_threshold'   => 'nullable|numeric|between:0,100',
        ];
    }
}
