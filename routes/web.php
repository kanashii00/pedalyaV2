<?php

use App\Http\Controllers\Admin\AccidentController as AdminAccident;
use App\Http\Controllers\Admin\AuditController as AdminAudit;
use App\Http\Controllers\Admin\BicycleController as AdminBicycle;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GeofenceController as AdminGeofence;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenance;
use App\Http\Controllers\Admin\MonitoringController as AdminMonitoring;
use App\Http\Controllers\Admin\NotificationController as AdminNotifications;
use App\Http\Controllers\Admin\PaymentController as AdminPayment;
use App\Http\Controllers\Admin\PaymentWebhookController as AdminPaymentWebhook;
use App\Http\Controllers\Admin\RentalController as AdminRental;
use App\Http\Controllers\Admin\ReportController as AdminReports;
use App\Http\Controllers\Admin\RiderController as AdminRider;
use App\Http\Controllers\Admin\SettingController as AdminSettings;
use App\Http\Controllers\Admin\TheftController as AdminTheft;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Rider\DashboardController as RiderDashboard;
use App\Http\Controllers\Rider\NotificationController as RiderNotifications;
use App\Http\Controllers\Rider\ProfileController as RiderProfile;
use App\Http\Controllers\Rider\RentController as RiderRent;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', fn () => view('index'))->name('home');

// Guest auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Google OAuth (web session)
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToProvider'])->name('login.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleProviderCallback'])->name('login.google.callback');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Bicycle management
    Route::get('/bicycles', [AdminBicycle::class, 'index'])->name('bicycles.index');
    Route::get('/bicycles/status', [AdminMonitoring::class, 'bicycleStatusIndex'])->name('bicycles.status');
    Route::get('/bicycles/create', [AdminBicycle::class, 'create'])->name('bicycles.create');
    Route::get('/bicycles/{id}', [AdminBicycle::class, 'show'])->name('bicycles.show');
    Route::post('/bicycles', [AdminBicycle::class, 'store'])->name('bicycles.store');
    Route::put('/bicycles/{id}', [AdminBicycle::class, 'update'])->name('bicycles.update');
    Route::delete('/bicycles/{id}', [AdminBicycle::class, 'destroy'])->name('bicycles.destroy');
    Route::post('/bicycles/{id}/lock', [AdminBicycle::class, 'lock'])->name('bicycles.lock');
    Route::get('/bicycles/{id}/telemetry', [AdminBicycle::class, 'telemetry'])->name('bicycles.telemetry');

    // Rider management
    Route::get('/riders', [AdminRider::class, 'index'])->name('riders.index');
    Route::get('/riders/create', [AdminRider::class, 'create'])->name('riders.create');
    Route::post('/riders', [AdminRider::class, 'store'])->name('riders.store');
    Route::put('/riders/{id}/verify', [AdminRider::class, 'verify'])->name('riders.verify');
    Route::put('/riders/{id}/status', [AdminRider::class, 'updateStatus'])->name('riders.status');
    Route::get('/verified-customers', [AdminRider::class, 'verified'])->name('riders.verified');
    Route::get('/blacklisted-customers', [AdminRider::class, 'blacklisted'])->name('riders.blacklisted');
    Route::put('/blacklisted-customers/{id}', [AdminRider::class, 'updateBlacklist'])->name('riders.blacklist.update');

    // Rental management
    Route::get('/rentals', [AdminRental::class, 'index'])->name('rentals.index');
    Route::get('/rentals/history', [AdminRental::class, 'history'])->name('rentals.history');
    Route::get('/rentals/returns', [AdminRental::class, 'returns'])->name('rentals.returns');
    Route::get('/rentals/{id}', [AdminRental::class, 'show'])->name('rentals.show');
    Route::put('/rentals/{id}/approve', [AdminRental::class, 'approve'])->name('rentals.approve');
    Route::put('/rentals/{id}/verify-gcash', [AdminRental::class, 'verifyGcashPayment'])->name('rentals.verify-gcash');
    Route::put('/rentals/{id}/mark-paid', [AdminRental::class, 'markPaid'])->name('rentals.mark-paid');
    Route::put('/rentals/{id}/end-ride', [AdminRental::class, 'endRide'])->name('rentals.end-ride');
    Route::put('/rentals/{id}/process-return', [AdminRental::class, 'processReturn'])->name('rentals.process-return');
    Route::put('/rentals/{id}/cancel', [AdminRental::class, 'cancel'])->name('rentals.cancel');

    // Monitoring
    Route::get('/monitoring', [AdminMonitoring::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/live', [AdminMonitoring::class, 'live'])->name('monitoring.live');
    Route::get('/monitoring/{id}/status', [AdminMonitoring::class, 'bicycleStatus'])->name('monitoring.status');

    // Geofence management
    Route::get('/geofence', [AdminGeofence::class, 'index'])->name('geofence.index');
    Route::put('/geofence', [AdminGeofence::class, 'update'])->name('geofence.update');

    // Theft alerts
Route::get('/theft', [AdminTheft::class, 'index'])->name('theft-alerts.index');
Route::get('/theft/live', [AdminTheft::class, 'live'])->name('theft-alerts.live');
Route::post('/theft/{id}/acknowledge', [AdminTheft::class, 'acknowledge'])->name('theft-alerts.acknowledge');

    // Accidents
    Route::get('/accidents', [AdminAccident::class, 'index'])->name('accidents.index');
    Route::post('/accidents/{id}/acknowledge', [AdminAccident::class, 'acknowledge'])->name('accidents.acknowledge');

    // Maintenance
    Route::get('/maintenance', [AdminMaintenance::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [AdminMaintenance::class, 'store'])->name('maintenance.store');
    Route::put('/maintenance/{id}', [AdminMaintenance::class, 'update'])->name('maintenance.update');
    Route::post('/maintenance/{id}/status', [AdminMaintenance::class, 'updateStatus'])->name('maintenance.updateStatus');

    // Notifications
    Route::get('/notifications', [AdminNotifications::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [AdminNotifications::class, 'store'])->name('notifications.store');

    // Audit log
    Route::get('/audit', [AdminAudit::class, 'index'])->name('audit-log.index');

    // System settings
    Route::get('/settings', [AdminSettings::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettings::class, 'update'])->name('settings.update');

    // Reports
    Route::get('/reports', [AdminReports::class, 'index'])->name('reports.index');
    Route::post('/reports/customer', [AdminReports::class, 'customerReport'])->name('reports.customer');
    Route::post('/reports/rental', [AdminReports::class, 'rentalReport'])->name('reports.rental');
    Route::post('/reports/revenue', [AdminReports::class, 'revenueReport'])->name('reports.revenue');
    Route::post('/reports/incident', [AdminReports::class, 'incidentReport'])->name('reports.incident');
    Route::post('/reports/accident', [AdminReports::class, 'accidentReport'])->name('reports.accident');
    Route::post('/reports/bicycle', [AdminReports::class, 'bicycleReport'])->name('reports.bicycle');
    Route::post('/reports/theft', [AdminReports::class, 'theftReport'])->name('reports.theft');
    Route::get('/reports/export/pdf', [AdminReports::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [AdminReports::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/csv', [AdminReports::class, 'exportCsv'])->name('reports.export.csv');

    // Payment Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [AdminPayment::class, 'index'])->name('index');
        Route::get('/create', [AdminPayment::class, 'create'])->name('create');
        Route::post('/', [AdminPayment::class, 'store'])->name('store');
        Route::get('/{payment}', [AdminPayment::class, 'show'])->name('show');
        Route::get('/{payment}/success', [AdminPayment::class, 'success'])->name('success');
        Route::get('/{payment}/cancel', [AdminPayment::class, 'cancel'])->name('cancel');
        Route::post('/{payment}/verify', [AdminPayment::class, 'verify'])->name('verify');
        Route::get('/{payment}/receipt', [AdminPayment::class, 'receipt'])->name('receipt');
    });

    // Payment Webhooks
    Route::post('/payments/webhook', [AdminPaymentWebhook::class, 'handle'])->name('payments.webhook');
});

// Rider routes
Route::middleware(['auth', 'role:rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/dashboard', [RiderDashboard::class, 'index'])->name('dashboard');

    // Rent bicycle
    Route::get('/rent', [RiderRent::class, 'index'])->name('rentals.create');
    Route::post('/rent', [RiderRent::class, 'store'])->name('rent.store');
    Route::post('/rent/{id}/return', [RiderRent::class, 'returnRental'])->name('rentals.return');

    // Rental history
    Route::get('/history', [RiderRent::class, 'history'])->name('rentals.index');

    // Profile
    Route::get('/profile', [RiderProfile::class, 'show'])->name('profile.index');
    Route::put('/profile', [RiderProfile::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-id', [RiderProfile::class, 'uploadId'])->name('profile.upload-id');
    Route::put('/profile/password', [RiderProfile::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [RiderProfile::class, 'destroy'])->name('profile.delete');

    // Notifications
    Route::get('/notifications', [RiderNotifications::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [RiderNotifications::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [RiderNotifications::class, 'markAllRead'])->name('notifications.mark-all-read');
});
