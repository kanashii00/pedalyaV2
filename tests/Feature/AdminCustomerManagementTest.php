<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_lists_and_filters_riders(): void
    {
        $admin = $this->makeAdmin();
        $this->makeRider(['name' => 'Alice', 'status' => User::STATUS_ACTIVE]);
        $this->makeRider(['name' => 'Bob', 'status' => User::STATUS_SUSPENDED]);

        $this->actingAs($admin)
            ->get(route('admin.riders.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.riders.index', ['status' => 'suspended', 'search' => 'Bob', 'verified' => 1]))
            ->assertOk();
    }

    public function test_create_returns_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.riders.create'))
            ->assertOk();
    }

    public function test_store_creates_rider_and_audit_log(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.riders.store'), [
                'name' => 'New Rider',
                'email' => 'stored-rider@pedalya.test',
                'phoneNumber' => '09171234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.riders.index'));

        $this->assertDatabaseHas('users', ['email' => 'stored-rider@pedalya.test', 'role' => User::ROLE_RIDER]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'rider_created']);
    }

    public function test_store_validates_duplicate_email(): void
    {
        $admin = $this->makeAdmin();
        $this->makeRider(['email' => 'dup@pedalya.test']);

        $this->actingAs($admin)
            ->post(route('admin.riders.store'), [
                'name' => 'X',
                'email' => 'dup@pedalya.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_verify_approves_rider_and_notifies(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider(['verified' => false]);

        $this->from(route('admin.riders.index'))
            ->actingAs($admin)
            ->put(route('admin.riders.verify', $rider->id), ['approved' => 1])
            ->assertRedirect(route('admin.riders.index'));

        $this->assertTrue($rider->fresh()->verified);
        $this->assertDatabaseHas('audit_logs', ['action' => 'rider_verification_approved']);
        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'type' => 'verification_approved']);
    }

    public function test_verify_rejects_rider_with_reason(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider(['verified' => true]);

        $this->from(route('admin.riders.index'))
            ->actingAs($admin)
            ->put(route('admin.riders.verify', $rider->id), ['approved' => 0, 'reason' => 'Blurry'])
            ->assertRedirect(route('admin.riders.index'));

        $this->assertFalse($rider->fresh()->verified);
        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'type' => 'verification_rejected']);
    }

    public function test_update_status_success(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider();

        $this->from(route('admin.riders.index'))
            ->actingAs($admin)
            ->put(route('admin.riders.status', $rider->id), ['status' => 'suspended'])
            ->assertRedirect(route('admin.riders.index'));

        $this->assertSame('suspended', $rider->fresh()->status);
    }

    public function test_update_status_to_active_clears_blacklist_reason(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider(['status' => 'blacklisted', 'blacklistReason' => 'Past issue']);

        $this->from(route('admin.riders.index'))
            ->actingAs($admin)
            ->put(route('admin.riders.status', $rider->id), ['status' => 'active'])
            ->assertRedirect(route('admin.riders.index'));

        $fresh = $rider->fresh();
        $this->assertSame('active', $fresh->status);
        $this->assertNull($fresh->blacklistReason);
    }

    public function test_update_status_blocked_when_active_rental_exists(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider();
        $this->makeRental(['riderId' => $rider->id, 'status' => Rental::STATUS_ACTIVE]);

        $this->from(route('admin.riders.index'))
            ->actingAs($admin)
            ->put(route('admin.riders.status', $rider->id), ['status' => 'suspended'])
            ->assertSessionHasErrors('status');
    }

    public function test_verified_and_blacklisted_pages(): void
    {
        $admin = $this->makeAdmin();
        $this->makeRider(['verified' => true, 'status' => User::STATUS_ACTIVE]);
        $this->makeRider(['verified' => false, 'status' => 'blacklisted']);

        $this->actingAs($admin)->get(route('admin.riders.verified'))->assertOk();
        $this->actingAs($admin)->get(route('admin.riders.blacklisted'))->assertOk();
        $this->actingAs($admin)->get(route('admin.riders.blacklisted', ['status' => 'blacklisted', 'search' => 'x']))->assertOk();
    }

    public function test_update_blacklist_validates_and_updates(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider(['status' => 'blacklisted']);

        $this->from(route('admin.riders.blacklisted'))
            ->actingAs($admin)
            ->put(route('admin.riders.blacklist.update', $rider->id), [
                'name' => 'Updated',
                'email' => $rider->email,
                'status' => 'suspended',
                'blacklistReason' => 'Violations',
            ])
            ->assertRedirect(route('admin.riders.blacklisted'));

        $fresh = $rider->fresh();
        $this->assertSame('suspended', $fresh->status);
        $this->assertSame('Violations', $fresh->blacklistReason);
        $this->assertDatabaseHas('audit_logs', ['action' => 'rider_blacklist_updated']);
    }

    public function test_admin_routes_reject_non_admin(): void
    {
        $rider = $this->makeRider();

        $this->actingAs($rider)
            ->get(route('admin.riders.index'))
            ->assertRedirect();
    }

    public function test_unauthenticated_admin_routes_redirect_to_login(): void
    {
        $this->get(route('admin.riders.index'))->assertRedirect(route('login'));
    }
}
