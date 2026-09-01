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

    public function test_get_config_exposes_shape_fields(): void
    {
        Geofence::create([
            'name' => 'Zone',
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'radius' => 200,
            'shapeType' => 'oval_h',
            'width' => 1600,
            'height' => 900,
            'rotation' => 0,
            'isActive' => true,
        ]);

        $config = $this->service->getConfig();

        $this->assertSame('oval_h', $config['shapeType']);
        $this->assertSame(1600.0, $config['width']);
        $this->assertSame(900.0, $config['height']);
    }

    public function test_check_point_rectangle_uses_width_height(): void
    {
        $config = [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'rectangle',
            'width' => 2000, // a = 1000 east-west
            'height' => 1000, // b = 500 north-south
            'rotation' => 0,
            'warningThreshold' => 100,
        ];

        // 500m east: inside (500 <= 1000)
        $inside = $this->service->checkPoint(14.6, 120.9946425, $config);
        $this->assertTrue($inside['inside']);

        // 1500m east: outside (1500 > 1000)
        $out = $this->service->checkPoint(14.6, 121.0039300, $config);
        $this->assertFalse($out['inside']);
        $this->assertSame('breach', $out['level']);

        // 900m north: outside (900 > 500)
        $north = $this->service->checkPoint(14.6080900, 120.99, $config);
        $this->assertFalse($north['inside']);
    }

    public function test_check_point_rectangle_respects_rotation(): void
    {
        $config = [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'rectangle',
            'width' => 2000,
            'height' => 1000,
            'rotation' => 0,
            'warningThreshold' => 100,
        ];

        // 700m north is outside unrotated (700 > b=500)
        $point = ['lat' => 14.6062900, 'lng' => 120.99];
        $unrotated = $this->service->checkPoint($point['lat'], $point['lng'], $config);
        $this->assertFalse($unrotated['inside']);

        // Rotate 90° -> the 2000m axis now points north, so 700m north is inside
        $rotated = $this->service->checkPoint($point['lat'], $point['lng'], [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'rectangle',
            'width' => 2000,
            'height' => 1000,
            'rotation' => 90,
            'warningThreshold' => 100,
        ]);
        $this->assertTrue($rotated['inside']);
    }

    public function test_check_point_oval_uses_width_height(): void
    {
        $config = [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'oval_h',
            'width' => 2000, // a = 1000
            'height' => 1200, // b = 600
            'rotation' => 0,
            'warningThreshold' => 100,
        ];

        // 900m east: (900/1000)^2 = 0.81 <= 1 -> inside
        $in = $this->service->checkPoint(14.6, 120.998356, $config);
        $this->assertTrue($in['inside']);

        // 1100m east: 1.21 > 1 -> outside
        $out = $this->service->checkPoint(14.6, 121.000211, $config);
        $this->assertFalse($out['inside']);
    }

    public function test_check_point_polygon(): void
    {
        $config = [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'polygon',
            'points' => [
                ['lat' => 14.6, 'lng' => 120.98],
                ['lat' => 14.6, 'lng' => 121.00],
                ['lat' => 14.7, 'lng' => 121.00],
                ['lat' => 14.7, 'lng' => 120.98],
            ],
            'warningThreshold' => 100,
        ];

        $this->assertTrue($this->service->checkPoint(14.65, 120.99, $config)['inside']);
        $this->assertFalse($this->service->checkPoint(15.0, 121.5, $config)['inside']);
        $this->assertSame('breach', $this->service->checkPoint(15.0, 121.5, $config)['level']);
    }

    public function test_check_point_deep_warning_near_boundary_for_oval(): void
    {
        $config = [
            'centerLat' => 14.6,
            'centerLng' => 120.99,
            'shapeType' => 'oval_h',
            'width' => 2000,
            'height' => 1200,
            'rotation' => 0,
            'warningThreshold' => 100,
        ];

        // Just inside the ellipse boundary east -> should be approaching/warning, not safe.
        // At y=0 the boundary is ~1000m east; pick 950m so distanceToBoundary ~= 50m < threshold.
        $near = $this->service->checkPoint(14.6, 120.99882, $config);
        $this->assertTrue($near['inside']);
        $this->assertNotSame('safe', $near['level']);
        $this->assertLessThan(100, $near['distanceToBoundary']);
    }
}
