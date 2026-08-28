<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);
    }

    private function fakeDriver(?SocialiteUser $user = null, bool $fail = false): Provider
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturn($provider);
        $provider->shouldReceive('with')->andReturn($provider);
        $provider->shouldReceive('stateless')->andReturn($provider);
        $provider->shouldReceive('redirect')->andReturn(redirect('http://google.test/authorize'));

        if ($fail) {
            $provider->shouldReceive('user')->andThrow(new \RuntimeException('oauth failed'));
        } else {
            $provider->shouldReceive('user')->andReturn($user);
        }

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        return $provider;
    }

    private function googleUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->id = $overrides['id'] ?? 'goog-123';
        $user->email = $overrides['email'] ?? 'google@pedalya.test';
        $user->name = $overrides['name'] ?? 'Google Rider';
        $user->nickname = $overrides['nickname'] ?? null;
        $user->avatar = $overrides['avatar'] ?? 'https://x/avatar.png';
        return $user;
    }

    // ---- Web flow ----

    public function test_web_redirect_redirects_to_google(): void
    {
        $this->fakeDriver();
        $this->get(route('login.google'))->assertRedirect();
    }

    public function test_web_callback_new_user_goes_to_register(): void
    {
        $this->fakeDriver($this->googleUser());

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('pending_oauth');
    }

    public function test_web_callback_existing_rider_logs_in(): void
    {
        $rider = $this->makeRider(['email' => 'google@pedalya.test']);
        $this->fakeDriver($this->googleUser());

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('rider.dashboard'));

        $this->assertAuthenticatedAs($rider);
        $this->assertDatabaseHas('users', ['id' => $rider->id, 'google_id' => 'goog-123']);
    }

    public function test_web_callback_existing_admin_logs_in(): void
    {
        $admin = $this->makeAdmin(['email' => 'google@pedalya.test']);
        $this->fakeDriver($this->googleUser());

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_web_callback_inactive_user_rejected(): void
    {
        $this->makeRider(['email' => 'google@pedalya.test', 'status' => User::STATUS_INACTIVE]);
        $this->fakeDriver($this->googleUser());

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_web_callback_failure_redirects_to_login(): void
    {
        $this->fakeDriver(fail: true);
        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_web_redirect_without_config_aborts(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
        $this->get(route('login.google'))->assertStatus(500);
    }

    // ---- API flow ----

    public function test_api_redirect_returns_url(): void
    {
        $this->fakeDriver();
        $this->getJson('/api/auth/google/redirect')->assertOk()->assertJsonPath('url', 'http://google.test/authorize');
    }

    public function test_api_callback_new_user_needs_registration(): void
    {
        $this->fakeDriver($this->googleUser());

        $this->getJson('/api/auth/google/callback')
            ->assertOk()
            ->assertJson(['needs_registration' => true]);
    }

    public function test_api_callback_existing_user_returns_token(): void
    {
        $rider = $this->makeRider(['email' => 'google@pedalya.test']);
        $this->fakeDriver($this->googleUser());

        $this->getJson('/api/auth/google/callback?device_name=mobile')
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type' => [], 'user']);
        $this->assertDatabaseHas('users', ['id' => $rider->id, 'google_id' => 'goog-123']);
    }

    public function test_api_callback_inactive_user_403(): void
    {
        $this->makeRider(['email' => 'google@pedalya.test', 'status' => User::STATUS_INACTIVE]);
        $this->fakeDriver($this->googleUser());

        $this->getJson('/api/auth/google/callback')->assertStatus(403);
    }

    public function test_api_callback_failure_401(): void
    {
        $this->fakeDriver(fail: true);
        $this->getJson('/api/auth/google/callback')->assertStatus(401);
    }
}
