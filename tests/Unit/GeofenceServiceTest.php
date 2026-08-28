<?php

namespace Tests\Unit;

use App\Models\Geofence;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GeofenceService::class);
    }

    public function test_get_config_returns_defaults_when_no_active_geofence(): void
    {
        $config = $this->service->getConfig();

        $this->assertArrayHasKey('centerLat', $config);
        $this->assertArrayHasKey('centerLng', $config);
        $this->assertArrayHasKey('radius', $config);
        $this->assertArrayHasKey('alertEnabled', $config);
        $this->assertArrayHasKey('warningThreshold', $config);
        $this->assertNull($config['id']);
        $this->assertTrue($config['alertEnabled']);
    }

    public function test_get_config_returns_active_geofence(): void
    {
        Geofence::create([
            'name' => 'Campus Zone',
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'radius' => 800,
            'isActive' => true,
            'alertEnabled' => true,
            'warningThreshold' => 150,
        ]);

        $config = $this->service->getConfig();

        $this->assertSame(14.6, $config['centerLat']);
        $this->assertSame(800.0, $config['radius']);
        $this->assertSame(150.0, $config['warningThreshold']);
        $this->assertNotNull($config['id']);
    }

    public function test_check_point_inside_returns_safe(): void
    {
        $result = $this->service->checkPoint(14.6, 120.99, [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'radius' => 500,
            'warningThreshold' => 100,
        ]);

        $this->assertTrue($result['inside']);
        $this->assertSame('safe', $result['level']);
        $this->assertFalse($result['warning']);
    }

    public function test_check_point_outside_returns_breach(): void
    {
        $result = $this->service->checkPoint(15.2, 121.5, [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'radius' => 100,
            'warningThreshold' => 50,
        ]);

        $this->assertFalse($result['inside']);
        $this->assertSame('breach', $result['level']);
        $this->assertTrue($result['warning']);
        $this->assertGreaterThan(0, $result['distanceOutside']);
    }

    public function test_check_point_uses_aliases_lat_lng(): void
    {
        $result = $this->service->checkPoint(14.6, 120.99, [
            'lat' => 14.6,
            'lng' => 120.99,
            'radius' => 500,
        ]);

        $this->assertTrue($result['inside']);
    }

    public function test_check_point_in_geofence_delegates_to_config(): void
    {
        Geofence::create([
            'name' => 'Campus Zone',
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'radius' => 500,
            'isActive' => true,
        ]);

        $result = $this->service->checkPointInGeofence(14.6, 120.99);

        $this->assertTrue($result['inside']);
    }

    public function test_calculate_distance_to_boundary(): void
    {
        $result = $this->service->calculateDistanceToBoundary(14.6, 120.99, [
            'lat' => 14.6,
            'lng' => 120.99,
        ], 500);

        $this->assertArrayHasKey('distanceToCenter', $result);
        $this->assertArrayHasKey('distanceToBoundary', $result);
        $this->assertTrue($result['inside']);
        $this->assertSame(0.0, $result['ratio']);
    }

    public function test_generate_geofence_warning_levels(): void
    {
        $this->assertSame('safe', $this->service->generateGeofenceWarning(10, 100)['level']);
        $this->assertSame('approaching', $this->service->generateGeofenceWarning(60, 100)['level']);
        $this->assertSame('warning', $this->service->generateGeofenceWarning(90, 100)['level']);
        $this->assertSame('breach', $this->service->generateGeofenceWarning(120, 100)['level']);
        $this->assertTrue($this->service->generateGeofenceWarning(120, 100)['breach']);
    }

    public function test_haversine_distance_is_positive_for_distinct_points(): void
    {
        $distance = $this->service->haversineDistance(14.6, 120.99, 14.7, 121.0);

        $this->assertGreaterThan(0, $distance);
    }

    public function test_is_point_in_polygon(): void
    {
        $polygon = [
            ['lat' => 14.6, 'lng' => 120.98],
            ['lat' => 14.6, 'lng' => 121.00],
            ['lat' => 14.7, 'lng' => 121.00],
            ['lat' => 14.7, 'lng' => 120.98],
        ];

        $this->assertTrue($this->service->isPointInPolygon(14.65, 120.99, $polygon));
        $this->assertFalse($this->service->isPointInPolygon(15.0, 121.5, $polygon));
    }

    public function test_calculate_bounding_box(): void
    {
        $box = $this->service->calculateBoundingBox(14.6, 120.99, 1000);

        $this->assertLessThan(14.6, $box['minLat']);
        $this->assertGreaterThan(14.6, $box['maxLat']);
        $this->assertLessThan(120.99, $box['minLng']);
        $this->assertGreaterThan(120.99, $box['maxLng']);
    }
}
