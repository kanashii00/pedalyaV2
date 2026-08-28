<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class WebRegisteredUserTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_create_renders_register_form(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_register_without_oauth_requires_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New Rider',
            'email' => 'newrider@pedalya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('rider.dashboard'));

        $user = User::where('email', 'newrider@pedalya.test')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_RIDER, $user->role);
        $this->assertNotNull($user->password);
    }

    public function test_register_without_oauth_rejects_missing_password(): void
    {
        $this->post(route('register'), [
            'name' => 'New Rider',
            'email' => 'newrider2@pedalya.test',
        ])->assertSessionHasErrors('password');
    }

    public function test_register_with_oauth_sets_google_fields(): void
    {
        $this->withSession(['pending_oauth' => ['google_id' => 'goog-123', 'avatar' => 'https://x/a.png']])
            ->post(route('register'), [
                'name' => 'OAuth Rider',
                'email' => 'oauth@pedalya.test',
                'phoneNumber' => '09171234567',
            ])
            ->assertRedirect(route('rider.dashboard'));

        $user = User::where('email', 'oauth@pedalya.test')->first();
        $this->assertSame('goog-123', $user->google_id);
        $this->assertSame('google', $user->oauth_provider);
        $this->assertNull($user->password);
        $this->assertNull($this->app['session']->get('pending_oauth'));
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $this->makeRider(['email' => 'dup@pedalya.test']);

        $this->post(route('register'), [
            'name' => 'Dup',
            'email' => 'dup@pedalya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }
}
