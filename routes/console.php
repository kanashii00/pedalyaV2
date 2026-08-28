<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// IoT / safety automation
Schedule::command('rentals:check-expiry-warnings')->everyMinute()->withoutOverlapping();
Schedule::command('rentals:check-overdue')->everyMinute()->withoutOverlapping();
Schedule::command('rentals:check-grace-locks')
    ->everyMinute()
    ->withoutOverlapping();
Schedule::command('devices:check-inactive')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('gps:cleanup --days=90')->daily();
