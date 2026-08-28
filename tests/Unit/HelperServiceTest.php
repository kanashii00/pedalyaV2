<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\HelperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class HelperServiceTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_calculate_distance_returns_positive(): void
    {
        $distance = HelperService::calculateDistance(14.6, 120.99, 14.7, 121.0);
        $this->assertGreaterThan(0, $distance);

        $zero = HelperService::calculateDistance(14.6, 120.99, 14.6, 120.99);
        $this->assertLessThan(1e-6, $zero);
    }

    public function test_validate_coordinates_valid(): void
    {
        $result = HelperService::validateCoordinates('14.6', '120.99');

        $this->assertTrue($result['valid']);
        $this->assertSame(14.6, $result['lat']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validate_coordinates_rejects_non_numeric(): void
    {
        $result = HelperService::validateCoordinates('abc', 'xyz');

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validate_coordinates_rejects_out_of_range(): void
    {
        $result = HelperService::validateCoordinates(95, 200);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_generate_id_has_prefix_and_unique(): void
    {
        $a = HelperService::generateId('REN');
        $b = HelperService::generateId('REN');

        $this->assertStringStartsWith('REN-', $a);
        $this->assertNotSame($a, $b);
    }

    public function test_generate_id_without_prefix(): void
    {
        $id = HelperService::generateId();

        $this->assertStringNotContainsString('-REN', $id);
        $this->assertNotEmpty($id);
    }

    public function test_sanitize_user_data(): void
    {
        $user = $this->makeRider();
        $user->phone = '09171234567';

        $sanitized = HelperService::sanitizeUserData($user);

        $this->assertSame($user->id, $sanitized['id']);
        $this->assertSame($user->email, $sanitized['email']);
        $this->assertSame('09171234567', $sanitized['phone']);
        $this->assertArrayHasKey('role', $sanitized);
        $this->assertArrayNotHasKey('password', $sanitized);
    }
}
