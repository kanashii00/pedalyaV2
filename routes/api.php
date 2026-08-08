<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BicycleController as ApiBicycle;
use App\Http\Controllers\Api\RentalController as ApiRental;
use App\Http\Controllers\Api\GpsController as ApiGps;
use App\Http\Controllers\Api\IoTController as ApiIoT;
use App\Http\Controllers\Api\NotificationController as ApiNotification;
use App\Http\Controllers\Api\AuthController as ApiAuth;

// Health check
Route::get('/health', fn () => response()->json([
    'success' => true,
    'data' => ['status' => 'healthy', 'service' => 'Pedalya IoT Bicycle Rental API', 'version' => '2.0.0', 'timestamp' => now()->toIso8601String()]
]));

// Public auth
Route::post('/auth/login', [ApiAuth::class, 'login'])->middleware('throttle:10,1');
Route::post('/auth/register', [ApiAuth::class, 'register'])->middleware('throttle:5,1');

// Device-authenticated routes (IoT devices / ESP32)
Route::middleware('device.auth')->group(function () {
    Route::post('/iot/heartbeat', [ApiIoT::class, 'heartbeat']);
    Route::post('/iot/accident-report', [ApiIoT::class, 'accidentReport']);
    Route::post('/iot/geofence-alert', [ApiIoT::class, 'geofenceAlert']);
    Route::post('/iot/bicycle/{id}/status', [ApiIoT::class, 'bicycleStatus']);
    Route::post('/iot/bicycle/{id}/command-ack', [ApiIoT::class, 'acknowledgeCommand']);

    Route::post('/gps/location', [ApiGps::class, 'location']);
});

// Auth-protected API routes (Sanctum bearer tokens)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::get('/auth/profile', [ApiAuth::class, 'profile']);
    Route::put('/auth/profile', [ApiAuth::class, 'updateProfile']);
    Route::post('/auth/logout', [ApiAuth::class, 'logout']);

    // Bicycles
    Route::get('/bicycles/nearby', [ApiBicycle::class, 'nearby']);
    Route::get('/bicycles', [ApiBicycle::class, 'index']);
    Route::get('/bicycles/{id}', [ApiBicycle::class, 'show']);
    Route::post('/bicycles', [ApiBicycle::class, 'store'])->middleware('role:admin');
    Route::put('/bicycles/{id}', [ApiBicycle::class, 'update'])->middleware('role:admin');
    Route::delete('/bicycles/{id}', [ApiBicycle::class, 'destroy'])->middleware('role:admin');
    Route::post('/bicycles/{id}/lock', [ApiBicycle::class, 'lock'])->middleware('role:admin');
    Route::get('/bicycles/{id}/telemetry', [ApiBicycle::class, 'telemetry']);

    // Rentals
    Route::get('/rentals/active', [ApiRental::class, 'active']);
    Route::get('/rentals', [ApiRental::class, 'index']);
    Route::get('/rentals/{id}', [ApiRental::class, 'show']);
    Route::post('/rentals', [ApiRental::class, 'store']);
    Route::put('/rentals/{id}/return', [ApiRental::class, 'returnRental']);
    Route::put('/rentals/{id}/approve', [ApiRental::class, 'approve'])->middleware('role:admin');
    Route::put('/rentals/{id}/cancel', [ApiRental::class, 'cancel']);

    // GPS (auth-protected for tracking)
    Route::get('/gps/bicycle/{id}/track', [ApiGps::class, 'track']);
    Route::get('/gps/bicycle/{id}/current', [ApiGps::class, 'current']);
    Route::post('/gps/geofence', [ApiGps::class, 'updateGeofence'])->middleware('role:admin');
    Route::get('/gps/geofence', [ApiGps::class, 'getGeofence']);

    // IoT (auth-protected for status/commands)
    Route::get('/iot/bicycle/{id}/status', [ApiIoT::class, 'bicycleStatusAuth']);
    Route::post('/iot/bicycle/{id}/command', [ApiIoT::class, 'command'])->middleware('role:admin');

    // Notifications
    Route::get('/notifications', [ApiNotification::class, 'index']);
    Route::get('/notifications/unread-count', [ApiNotification::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [ApiNotification::class, 'markRead']);
    Route::put('/notifications/read-all', [ApiNotification::class, 'markAllRead']);
});
