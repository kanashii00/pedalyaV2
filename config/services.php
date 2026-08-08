<?php

return [
    'device_api_key' => env('DEVICE_API_KEY', 'pedalya-iot-device-key-2024'),

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

    'geofence' => [
        'default_radius' => (int) env('GEOFENCE_DEFAULT_RADIUS', 500),
        'warning_threshold' => (int) env('GEOFENCE_WARNING_THRESHOLD', 100),
        'center_lat' => (float) env('GEOFENCE_CENTER_LAT', 14.5995),
        'center_lng' => (float) env('GEOFENCE_CENTER_LNG', 120.9842),
    ],

    'rental' => [
        'rate_per_hour' => (float) env('RENTAL_RATE_PER_HOUR', 15.00),
        'max_duration_hours' => (int) env('RENTAL_MAX_DURATION_HOURS', 12),
        'deposit_amount' => (float) env('DEPOSIT_AMOUNT', 100.00),
    ],

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
        'timeout' => (int) env('PAYMONGO_TIMEOUT', 30),
    ],
];
