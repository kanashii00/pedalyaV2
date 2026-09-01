<?php

namespace App\Providers;

use App\Models\MaintenanceRecord;
use App\Models\Rental;
use App\Observers\MaintenanceRecordObserver;
use App\Observers\RentalObserver;
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
    }
}
