<?php

namespace Tests\Unit;

use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use App\Services\MaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class MaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MaintenanceService::class);
    }

    public function test_place_bicycle_in_maintenance_creates_record_and_sets_status(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $record = $this->service->placeBicycleInMaintenance($bike, 'Gear issue', $admin->id);

        $this->assertSame($bike->id, $record->bicycleId);
        $this->assertSame('Gear issue', $record->description);
        $this->assertSame(MaintenanceRecord::STATUS_SCHEDULED, $record->status);
        $this->assertSame($admin->id, $record->createdBy);

        $bike->refresh();
        $this->assertSame(Bicycle::STATUS_MAINTENANCE, $bike->status);
        $this->assertSame(Bicycle::LOCK_LOCKED, $bike->lockStatus);
    }

    public function test_place_bicycle_in_maintenance_returns_existing_active_record(): void
    {
        $bike = $this->makeBicycle();
        $this->service->placeBicycleInMaintenance($bike, 'first');

        $second = $this->service->placeBicycleInMaintenance($bike, 'second');

        $this->assertSame('first', $second->description);
        $this->assertSame(1, MaintenanceRecord::count());
    }

    public function test_can_release_bicycle_when_no_active_records(): void
    {
        $bike = $this->makeBicycle();

        $this->assertTrue($this->service->canReleaseBicycle($bike));
    }

    public function test_can_release_bicycle_false_while_active_record_exists(): void
    {
        $bike = $this->makeBicycle();
        $record = $this->service->placeBicycleInMaintenance($bike);

        $this->assertFalse($this->service->canReleaseBicycle($bike));
        $this->assertTrue($this->service->canReleaseBicycle($bike, $record->id));
    }

    public function test_release_bicycle_for_completed_record(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $record = $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_COMPLETED,
        ]);

        $ok = $this->service->releaseBicycleForRecord($record);

        $this->assertTrue($ok);
        $bike->refresh();
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $bike->status);
        $this->assertSame('good', $bike->condition);
        $this->assertNotNull($bike->lastMaintenanceDate);
    }

    public function test_release_bicycle_returns_false_for_non_completed_status(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $record = $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_SCHEDULED,
        ]);

        $this->assertFalse($this->service->releaseBicycleForRecord($record));
    }

    public function test_release_bicycle_returns_false_when_bike_not_in_maintenance(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $record = $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_COMPLETED,
        ]);

        $this->assertFalse($this->service->releaseBicycleForRecord($record));
    }

    public function test_release_bicycle_returns_false_when_other_active_record_exists(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_SCHEDULED,
        ]);
        $completed = $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_COMPLETED,
        ]);

        $this->assertFalse($this->service->releaseBicycleForRecord($completed));
    }
}
