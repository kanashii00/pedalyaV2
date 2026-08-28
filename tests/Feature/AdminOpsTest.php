<?php

namespace Tests\Feature;

use App\Models\Accident;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\GeofenceBreach;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminOpsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['broadcasting.default' => 'log']);
    }

    private function adminAuth(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
    }

    // ---- Admin Dashboard ----

    public function test_admin_dashboard_renders(): void
    {
        $this->adminAuth();
        $this->makeBicycle(['batteryLevel' => 10]);
        $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);

        $this->get(route('admin.dashboard'))->assertOk();
    }

    // ---- Settings ----

    public function test_settings_index_renders(): void
    {
        $this->adminAuth();
        $this->get(route('admin.settings.index'))->assertOk();
    }

    public function test_settings_update_stores_values_and_updates_geofence(): void
    {
        $this->adminAuth();

        $this->put(route('admin.settings.update'), [
            'companyName' => 'Pedalya Corp',
            'rentalRatePerHour' => 25,
            'geofenceEnabled' => true,
            'geofenceCenterLat' => 7.0990,
            'geofenceCenterLng' => 125.6470,
            'geofenceRadius' => 500,
            'geofenceWarningThreshold' => 80,
        ])->assertOk()->assertJson(['message' => 'Settings updated successfully.']);

        $this->assertDatabaseHas('system_settings', ['key' => 'companyName']);
        $this->assertDatabaseHas('geofences', ['isActive' => true, 'radius' => 500]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'system_settings_updated']);
    }

    public function test_settings_update_with_existing_geofence_deactivates_others(): void
    {
        $this->adminAuth();
        $g1 = Geofence::create(['name' => 'A', 'centerLat' => 7.0, 'centerLng' => 125.6, 'radius' => 400, 'isActive' => true, 'alertEnabled' => true]);
        $g2 = Geofence::create(['name' => 'B', 'centerLat' => 7.0, 'centerLng' => 125.6, 'radius' => 400, 'isActive' => true, 'alertEnabled' => true]);

        $this->put(route('admin.settings.update'), [
            'geofenceRadius' => 300,
            'geofenceEnabled' => true,
            'geofenceCenterLat' => 7.0,
            'geofenceCenterLng' => 125.6,
        ])->assertOk();

        $this->assertDatabaseHas('geofences', ['id' => $g1->id, 'isActive' => true, 'radius' => 300]);
        $this->assertDatabaseHas('geofences', ['id' => $g2->id, 'isActive' => false]);
    }

    public function test_settings_update_validation_error(): void
    {
        $this->adminAuth();
        $this->put(route('admin.settings.update'), ['rentalRatePerHour' => -5])
            ->assertSessionHasErrors('rentalRatePerHour');
    }

    // ---- Accidents ----

    public function test_accidents_index_lists_incidents(): void
    {
        $this->adminAuth();
        $this->makeRider(['verified' => true]);
        $bike = $this->makeBicycle();
        Accident::create(['bicycleId' => $bike->id, 'type' => 'accident', 'severity' => 'major', 'description' => 'Collision']);

        $this->get(route('admin.accidents.index'))->assertOk();
    }

    public function test_accident_acknowledge(): void
    {
        $this->adminAuth();
        $bike = $this->makeBicycle();
        $inc = Accident::create(['bicycleId' => $bike->id, 'type' => 'impact_detected', 'severity' => 'moderate', 'acknowledged' => false]);

        $this->post(route('admin.accidents.acknowledge', $inc->id))
            ->assertRedirect();

        $this->assertDatabaseHas('accidents', ['id' => $inc->id, 'acknowledged' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'accident_acknowledged']);
    }

    // ---- Theft ----

    public function test_theft_index_lists_alerts(): void
    {
        $this->adminAuth();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);
        $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        Accident::create(['bicycleId' => $bike->id, 'type' => 'geofence_breach', 'severity' => 'major']);
        GeofenceBreach::create(['bicycleId' => $bike->id, 'lat' => 14.5995, 'lng' => 120.9842, 'distance' => 500, 'acknowledged' => false]);

        $this->get(route('admin.theft-alerts.index'))->assertOk();
    }

    public function test_theft_alert_acknowledge(): void
    {
        $this->adminAuth();
        $bike = $this->makeBicycle();
        $alert = Accident::create(['bicycleId' => $bike->id, 'type' => 'geofence_alert', 'severity' => 'major', 'acknowledged' => false]);
        $breach = GeofenceBreach::create(['bicycleId' => $bike->id, 'lat' => 14.5995, 'lng' => 120.9842, 'distance' => 500, 'acknowledged' => false]);

        $this->post(route('admin.theft-alerts.acknowledge', $alert->id))
            ->assertRedirect();

        $this->assertDatabaseHas('accidents', ['id' => $alert->id, 'acknowledged' => true]);
        $this->assertDatabaseHas('geofence_breaches', ['id' => $breach->id, 'acknowledged' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'theft_alert_acknowledged']);
    }

    // ---- Audit ----

    public function test_audit_index_with_filters(): void
    {
        $this->adminAuth();
        $user = $this->makeRider();
        AuditLog::record('test_action', $user->id, ['k' => 'v']);

        $this->get(route('admin.audit-log.index'))
            ->assertOk();

        $this->get(route('admin.audit-log.index', ['action' => 'test_action', 'userId' => $user->id, 'date_from' => now()->subDay()->toDateString(), 'date_to' => now()->addDay()->toDateString()]))
            ->assertOk();
    }

    // ---- Admin Notifications ----

    public function test_admin_notifications_index_renders(): void
    {
        $this->adminAuth();
        $this->makeRider();
        $this->get(route('admin.notifications.index'))->assertOk();
    }

    public function test_admin_notification_broadcast(): void
    {
        $this->adminAuth();
        $rider = $this->makeRider();

        $this->post(route('admin.notifications.store'), [
            'title' => 'Hello',
            'message' => 'World',
            'recipientType' => 'broadcast',
        ])->assertOk()->assertJson(['message' => 'Notification broadcast to all riders.']);

        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'title' => 'Hello']);
    }

    public function test_admin_notification_single(): void
    {
        $this->adminAuth();
        $rider = $this->makeRider();

        $this->post(route('admin.notifications.store'), [
            'title' => 'Single',
            'message' => 'Msg',
            'recipientType' => 'single',
            'user_id' => $rider->id,
        ])->assertOk();
    }

    public function test_admin_notification_multi(): void
    {
        $this->adminAuth();
        $r1 = $this->makeRider();
        $r2 = $this->makeRider();

        $this->post(route('admin.notifications.store'), [
            'title' => 'Multi',
            'message' => 'Msg',
            'recipientType' => 'multi',
            'user_ids' => [$r1->id, $r2->id],
        ])->assertOk();
    }

    public function test_admin_notification_validation_error(): void
    {
        $this->adminAuth();
        $this->post(route('admin.notifications.store'), ['title' => '', 'recipientType' => 'broadcast'])
            ->assertSessionHasErrors(['title', 'message']);
    }

    // ---- API Notifications ----

    public function test_api_notifications_index_with_filters(): void
    {
        $rider = $this->makeRider();
        Notification::create(['userId' => $rider->id, 'title' => 'A', 'message' => 'm', 'type' => 'general', 'read' => false]);

        $this->actingAs($rider, 'sanctum')
            ->getJson('/api/notifications?type=general&read=0&per_page=5')
            ->assertOk();
    }

    public function test_api_notifications_unread_count(): void
    {
        $rider = $this->makeRider();
        Notification::create(['userId' => $rider->id, 'title' => 'A', 'message' => 'm', 'read' => false]);
        Notification::create(['userId' => $rider->id, 'title' => 'B', 'message' => 'm', 'read' => true]);

        $this->actingAs($rider, 'sanctum')
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson(['unread_count' => 1]);
    }

    public function test_api_notifications_mark_read(): void
    {
        $rider = $this->makeRider();
        $n = Notification::create(['userId' => $rider->id, 'title' => 'A', 'message' => 'm', 'read' => false]);

        $this->actingAs($rider, 'sanctum')
            ->putJson("/api/notifications/{$n->id}/read")
            ->assertOk();

        $this->assertDatabaseHas('notifications', ['id' => $n->id, 'read' => true]);
    }

    public function test_api_notifications_mark_read_own_only(): void
    {
        $rider = $this->makeRider();
        $other = $this->makeRider();
        $n = Notification::create(['userId' => $other->id, 'title' => 'A', 'message' => 'm', 'read' => false]);

        $this->actingAs($rider, 'sanctum')
            ->putJson("/api/notifications/{$n->id}/read")
            ->assertStatus(404);
    }

    public function test_api_notifications_mark_all_read(): void
    {
        $rider = $this->makeRider();
        Notification::create(['userId' => $rider->id, 'title' => 'A', 'message' => 'm', 'read' => false]);
        Notification::create(['userId' => $rider->id, 'title' => 'B', 'message' => 'm', 'read' => false]);

        $this->actingAs($rider, 'sanctum')
            ->putJson('/api/notifications/read-all')
            ->assertOk();

        $this->assertSame(0, Notification::where('userId', $rider->id)->where('read', false)->count());
    }
}
