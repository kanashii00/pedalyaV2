<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_create_returns_notification(): void
    {
        $user = $this->makeRider();

        $notification = $this->service->create($user->id, 'New Rental', 'Bike unlocked', 'rental');

        $this->assertSame($user->id, $notification->userId);
        $this->assertSame('New Rental', $notification->title);
        $this->assertSame('rental', $notification->type);
        $this->assertFalse($notification->read);
    }

    public function test_create_with_extra_payload(): void
    {
        $user = $this->makeRider();
        $bike = $this->makeBicycle();

        $notification = $this->service->create($user->id, 'Alert', 'Message', 'system', ['bicycleId' => $bike->id]);

        $this->assertSame($bike->id, $notification->bicycleId);
    }

    public function test_create_for_users_creates_one_per_user(): void
    {
        $a = $this->makeRider();
        $b = $this->makeRider();

        $created = $this->service->createForUsers([$a->id, $b->id], 'Broadcast', 'Hello', 'system');

        $this->assertCount(2, $created);
        $this->assertSame(2, Notification::count());
    }

    public function test_mark_as_read_success_and_missing(): void
    {
        $user = $this->makeRider();
        $notification = $this->service->create($user->id, 'T', 'M', 'system');

        $this->assertTrue($this->service->markAsRead($notification->id, $user->id));
        $this->assertTrue($notification->refresh()->read);

        $this->assertFalse($this->service->markAsRead($notification->id, 99999));
        $this->assertFalse($this->service->markAsRead(99999, $user->id));
    }

    public function test_mark_all_as_read_returns_count(): void
    {
        $user = $this->makeRider();
        $this->service->create($user->id, 'A', 'M', 'system');
        $this->service->create($user->id, 'B', 'M', 'system');

        $this->assertSame(2, $this->service->markAllAsRead($user->id));
        $this->assertSame(0, $this->service->getUnreadCount($user->id));
    }

    public function test_get_unread_count_and_unread_for_user(): void
    {
        $user = $this->makeRider();
        $this->service->create($user->id, 'A', 'M', 'system');
        $this->service->create($user->id, 'B', 'M', 'system');
        $this->service->markAsRead($this->service->create($user->id, 'C', 'M', 'system')->id, $user->id);

        $this->assertSame(2, $this->service->getUnreadCount($user->id));
        $this->assertCount(2, $this->service->getUnreadForUser($user->id));
    }
}
