<?php

namespace App\Providers;

use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\MaintenanceRecord;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\SystemSetting;
use App\Models\User;
use App\Observers\BicycleObserver;
use App\Observers\GeofenceObserver;
use App\Observers\MaintenanceRecordObserver;
use App\Observers\NotificationObserver;
use App\Observers\PaymentObserver;
use App\Observers\RentalObserver;
use App\Observers\SystemSettingObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Automated settlement rule: rental Completed + Paid -> bicycle
        // Available, smart-lock controls Locked (RentalObserver).
        Rental::observe(RentalObserver::class);

        // Automated completion rule: maintenance record Completed ->
        // leaves the active list, bicycle Available again (MaintenanceRecordObserver).
        MaintenanceRecord::observe(MaintenanceRecordObserver::class);

        // Cache invalidation observers. Kept to the minimum surface needed to
        // keep cached catalog / summary data fresh without invalidating on
        // telemetry writes that arrive many times per second.
        Bicycle::observe(BicycleObserver::class);
        Geofence::observe(GeofenceObserver::class);
        SystemSetting::observe(SystemSettingObserver::class);
        Notification::observe(NotificationObserver::class);
        Payment::observe(PaymentObserver::class);
        User::observe(UserObserver::class);
    }
}
