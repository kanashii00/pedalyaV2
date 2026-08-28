<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\GpsLog;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ConsoleCommandsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_overdue_grace_locks_fails_without_admin(): void
    {
        $this->artisan('rentals:check-grace-locks')->assertExitCode(1);
    }

    public function test_overdue_grace_locks_succeeds_with_no_overdue_rentals(): void
    {
        $this->makeAdmin();
        $this->makeRider();

        $this->artisan('rentals:check-grace-locks')->assertSuccessful();
    }

    public function test_overdue_rentals_no_overdue_found(): void
    {
        $this->makeAdmin();
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'expectedEndTime' => now()->addHours(2),
        ]);

        $this->artisan('rentals:check-overdue')->assertSuccessful();
    }

    public function test_expiry_warnings_disabled_when_setting_zero(): void
    {
        $this->makeRider();
        \App\Models\SystemSetting::setValue('overdueBuzzerMinutes', 0);

        $this->artisan('rentals:check-expiry-warnings')->assertSuccessful();
    }

    public function test_expiry_warnings_fires_for_active_rental(): void
    {
        $this->makeAdmin();
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'endTime' => now()->addMinutes(4),
            'warningSentAt' => null,
        ]);

        $this->artisan('rentals:check-expiry-warnings')->assertSuccessful();

        $this->assertNotNull($rental->fresh()->warningSentAt);
        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'type' => 'rental_warning']);
        $this->assertDatabaseHas('device_commands', ['bicycleId' => $bike->id, 'command' => 'buzzer']);
        $this->assertDatabaseHas('device_commands', ['bicycleId' => $bike->id, 'command' => 'lcd']);
    }

    public function test_expiry_warnings_no_rentals_needed(): void
    {
        $this->makeRider();
        $this->makeRental(['startTime' => now()]);
        $this->artisan('rentals:check-expiry-warnings')->assertSuccessful();
    }

    public function test_monitor_inactive_devices_with_active_device(): void
    {
        $this->makeAdmin();
        $this->makeBicycle(['lastHeartbeat' => now()]);

        $this->artisan('devices:check-inactive')->assertSuccessful();
    }

    public function test_monitor_inactive_devices_notifies_for_stale_device(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['lastHeartbeat' => now()->subMinutes(30)]);

        $this->artisan('devices:check-inactive')->assertSuccessful();

        $this->assertDatabaseHas('notifications', ['type' => 'device_inactive']);
    }

    public function test_cleanup_old_gps_logs(): void
    {
        $this->makeBicycle();
        $log = GpsLog::create(['bicycleId' => 1, 'lat' => 1, 'lng' => 1, 'timestamp' => now()->subDays(200)]);
        $log->created_at = now()->subDays(200);
        $log->save();

        $this->artisan('gps:cleanup', ['--days' => 90])->assertSuccessful();
        $this->assertDatabaseMissing('gps_logs', ['id' => $log->id]);
    }
}
