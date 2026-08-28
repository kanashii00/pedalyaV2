<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DocumentUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_register_creates_rider_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Rider',
            'email' => 'new-rider@pedalya.test',
            'phoneNumber' => '09171234567',
            'address' => 'Test St',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Account created successfully.')
            ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'new-rider@pedalya.test', 'role' => User::ROLE_RIDER]);
    }

    public function test_register_with_invalid_data_fails(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'x',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422);
    }

    public function test_login_returns_token(): void
    {
        $this->makeRider(['email' => 'login@pedalya.test', 'password' => 'password123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@pedalya.test',
            'password' => 'password123',
            'device_name' => 'android',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $this->assertSame('Bearer', $response->json('token_type'));
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $this->makeRider(['email' => 'login2@pedalya.test', 'password' => 'password123']);

        $this->postJson('/api/auth/login', [
            'email' => 'login2@pedalya.test',
            'password' => 'wrongpass',
        ])->assertStatus(401);
    }

    public function test_login_inactive_user_returns_403(): void
    {
        $this->makeRider([
            'email' => 'inactive@pedalya.test',
            'password' => 'password123',
            'status' => User::STATUS_SUSPENDED,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'inactive@pedalya.test',
            'password' => 'password123',
        ])->assertStatus(403);
    }

    public function test_profile_returns_current_rental_and_rentals(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $response = $this->getJson('/api/auth/profile');

        $response->assertOk();
        $this->assertSame($rider->id, $response->json('data.id'));
    }

    public function test_update_profile(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $this->putJson('/api/auth/profile', [
            'name' => 'Updated Name',
            'phoneNumber' => '0922',
        ])->assertOk()->assertJsonPath('message', 'Profile updated successfully');

        $this->assertDatabaseHas('users', ['id' => $rider->id, 'name' => 'Updated Name']);
    }

    public function test_change_password_success_and_wrong_current(): void
    {
        $rider = $this->makeRider(['password' => 'oldpassword']);
        Sanctum::actingAs($rider);

        $this->putJson('/api/auth/password', [
            'current_password' => 'wrongpass',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);

        $this->putJson('/api/auth/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonPath('message', 'Password changed successfully.');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $rider->fresh()->password));
    }

    public function test_logout_deletes_token(): void
    {
        $rider = $this->makeRider();
        $token = $rider->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertSame(0, $rider->tokens()->count());
    }

    public function test_protected_route_requires_authentication(): void
    {
        $this->getJson('/api/auth/profile')->assertStatus(401);
    }

    public function test_upload_id_verification_with_mocked_service(): void
    {
        $rider = $this->makeRider();

        $documentService = Mockery::mock(DocumentUploadService::class);
        $documentService->shouldReceive('storeIdVerification')
            ->once()
            ->andReturn(['id_path' => '/ids/abc.jpg', 'id_url' => '/storage/ids/abc.jpg']);
        $this->app->instance(DocumentUploadService::class, $documentService);

        Sanctum::actingAs($rider);

        $this->postJson('/api/auth/id-verification', [
            'id_image_base64' => 'data:image/jpeg;base64,AAAA',
        ])->assertOk()->assertJsonPath('verification_status', 'pending');

        $rider->refresh();
        $this->assertTrue($rider->idUploaded);
        $this->assertSame('pending', $rider->idVerification['status']);
    }
}
