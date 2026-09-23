<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();

        config(['broadcasting.default' => 'log']);
        Cache::flush();
    }

    private function adminAuth()
    {
        $this->actingAs($this->makeAdmin());

        return $this;
    }

    // ---------- Successful sending ----------

    public function test_broadcast_sends_to_all_riders_and_not_admins(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $riderA = $this->makeRider();
        $riderB = $this->makeRider();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Notice',
            'message' => 'All riders, please be advised.',
            'type' => 'info',
            'recipientType' => 'broadcast',
        ])->assertOk()
          ->assertJson(['message' => 'Notification sent successfully.'])
          ->assertJsonPath('count', 2);

        $this->assertDatabaseHas('notifications', ['userId' => $riderA->id, 'title' => 'Notice', 'type' => 'info']);
        $this->assertDatabaseHas('notifications', ['userId' => $riderB->id, 'title' => 'Notice', 'type' => 'info']);
        $this->assertDatabaseMissing('notifications', ['userId' => $admin->id, 'title' => 'Notice']);
        $this->assertSame(2, Notification::where('title', 'Notice')->count());
    }

    public function test_single_sends_only_to_selected_user(): void
    {
        $this->adminAuth();
        $target = $this->makeRider();
        $somelese = $this->makeRider();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Personal',
            'message' => 'Only for you.',
            'type' => 'warning',
            'recipientType' => 'single',
            'user_id' => $target->id,
        ])->assertOk();

        $this->assertDatabaseHas('notifications', ['userId' => $target->id, 'title' => 'Personal']);
        $this->assertDatabaseMissing('notifications', ['userId' => $somelese->id, 'title' => 'Personal']);
        $this->assertSame(1, Notification::count());
    }

    public function test_multi_sends_only_to_selected_recipients(): void
    {
        $this->adminAuth();
        $r1 = $this->makeRider();
        $r2 = $this->makeRider();
        $excluded = $this->makeRider();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Group',
            'message' => 'For the group.',
            'type' => 'success',
            'recipientType' => 'multi',
            'user_ids' => [$r1->id, $r2->id],
        ])->assertOk()->assertJsonPath('count', 2);

        $this->assertDatabaseHas('notifications', ['userId' => $r1->id, 'title' => 'Group']);
        $this->assertDatabaseHas('notifications', ['userId' => $r2->id, 'title' => 'Group']);
        $this->assertDatabaseMissing('notifications', ['userId' => $excluded->id, 'title' => 'Group']);
    }

    public function test_non_ajax_form_post_redirects_with_flash_instead_of_raw_json(): void
    {
        $this->adminAuth();
        $rider = $this->makeRider();

        $this->post(route('admin.notifications.store'), [
            'title' => 'Plain form',
            'message' => 'No JavaScript available.',
            'type' => 'info',
            'recipientType' => 'single',
            'user_id' => $rider->id,
        ])->assertRedirect()->assertSessionHas('success', 'Notification sent successfully to 1 recipient(s).');

        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'title' => 'Plain form']);
    }

    // ---------- Validation failures ----------

    public function test_ajax_validation_failures_return_friendly_structured_errors(): void
    {
        $this->adminAuth();

        $response = $this->postJson(route('admin.notifications.store'), [
            'title' => '',
            'message' => '',
            'type' => 'bogus-type',
            'recipientType' => 'unknown',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');

        $this->assertStringStartsWith('Please enter a title.', $response->json('message'));

        $errors = $response->json('errors');
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('message', $errors);
        $this->assertArrayHasKey('type', $errors);
        $this->assertArrayHasKey('recipientType', $errors);
    }

    public function test_single_and_multi_require_recipients(): void
    {
        $this->adminAuth();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'No target',
            'message' => 'Nobody selected.',
            'type' => 'info',
            'recipientType' => 'single',
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'No group',
            'message' => 'Nobody selected.',
            'type' => 'info',
            'recipientType' => 'multi',
            'user_ids' => [],
        ])->assertStatus(422)->assertJsonValidationErrors('user_ids');
    }

    public function test_cannot_send_to_non_rider_recipient(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->makeRider();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Ops',
            'message' => 'Should be rejected.',
            'type' => 'info',
            'recipientType' => 'single',
            'user_id' => $admin->id,
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');
    }

    public function test_broadcast_with_zero_riders_returns_friendly_error(): void
    {
        $this->adminAuth();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Empty',
            'message' => 'No riders to reach.',
            'type' => 'info',
            'recipientType' => 'broadcast',
        ])->assertStatus(422)->assertJson(['message' => 'No recipients match this selection.']);
    }

    // ---------- Duplicate prevention ----------

    public function test_duplicate_button_click_does_not_create_duplicate_records(): void
    {
        $this->adminAuth();
        $r1 = $this->makeRider();
        $r2 = $this->makeRider();

        $payload = [
            'title' => 'Reminder',
            'message' => 'Please settle your balances.',
            'type' => 'warning',
            'recipientType' => 'multi',
            'user_ids' => [$r1->id, $r2->id],
        ];

        $this->postJson(route('admin.notifications.store'), $payload)->assertOk();

        $this->postJson(route('admin.notifications.store'), $payload)
            ->assertStatus(409)
            ->assertJson(['message' => 'This notification was already sent. Please check the Notifications list before sending again.']);

        $this->assertSame(2, Notification::where('title', 'Reminder')->count());
    }

    public function test_distinct_payloads_are_not_treated_as_duplicates(): void
    {
        $this->adminAuth();
        $rider = $this->makeRider();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'First',
            'message' => 'Message one.',
            'type' => 'info',
            'recipientType' => 'single',
            'user_id' => $rider->id,
        ])->assertOk();

        $this->postJson(route('admin.notifications.store'), [
            'title' => 'Second',
            'message' => 'Message two.',
            'type' => 'info',
            'recipientType' => 'single',
            'user_id' => $rider->id,
        ])->assertOk();

        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'title' => 'First']);
        $this->assertDatabaseHas('notifications', ['userId' => $rider->id, 'title' => 'Second']);
    }

    // ---------- Unread counts & marking as read ----------

    public function test_unread_count_reflects_database_and_decreases_when_marked_read(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJson(['unread_count' => 0]);

        $service = app(NotificationService::class);
        $service->create($admin->id, 'New Rental', 'A bike was rented.', 'rental_started');
        $service->create($admin->id, 'Alert', 'Theft detected.', 'theft');

        $this->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJson(['unread_count' => 2]);

        $notification = Notification::where('userId', $admin->id)->where('title', 'Alert')->first();
        $this->postJson(route('admin.notifications.mark-read', $notification->id))
            ->assertOk()
            ->assertJson(['message' => 'Notification marked as read.']);

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'read' => true]);
        $this->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJson(['unread_count' => 1]);
    }

    public function test_mark_read_only_affects_own_notifications(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $other = $this->makeRider();
        $foreign = Notification::create(['userId' => $other->id, 'title' => 'Foreign', 'message' => 'm', 'read' => false]);

        $this->postJson(route('admin.notifications.mark-read', $foreign->id))
            ->assertStatus(404);

        $this->assertDatabaseHas('notifications', ['id' => $foreign->id, 'read' => false]);
    }

    public function test_mark_all_read_zeroes_unread_count(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $service = app(NotificationService::class);
        $service->create($admin->id, 'A', 'm', 'system');
        $service->create($admin->id, 'B', 'm', 'system');

        $this->postJson(route('admin.notifications.mark-all-read'))
            ->assertOk()
            ->assertJson(['message' => '2 notification(s) marked as read.', 'unread_count' => 0]);

        $this->assertSame(0, Notification::where('userId', $admin->id)->where('read', false)->count());
        $this->getJson(route('admin.notifications.unread-count'))->assertJson(['unread_count' => 0]);
    }

    // ---------- Table & page ----------

    public function test_notifications_index_renders_with_unread_count(): void
    {
        $this->adminAuth()->makeRider();

        $this->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Send Notification');
    }

    public function test_notifications_table_partial_returns_html_for_ajax_refresh(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $rider = $this->makeRider();
        Notification::create(['userId' => $rider->id, 'title' => 'Hello', 'message' => 'World', 'type' => 'info', 'read' => false]);

        $this->getJson(route('admin.notifications.table'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}