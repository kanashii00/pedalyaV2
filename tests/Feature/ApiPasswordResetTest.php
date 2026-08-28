<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ApiPasswordResetTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_send_code_sends_email(): void
    {
        Mail::fake();
        $rider = $this->makeRider(['email' => 'reset@pedalya.test']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'reset@pedalya.test'])
            ->assertOk()
            ->assertJsonPath('message', 'Password reset code sent successfully.');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'reset@pedalya.test']);
    }

    public function test_send_code_rejects_unknown_email(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@pedalya.test'])->assertStatus(422);
    }

    public function test_send_code_validation(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'not-an-email'])->assertStatus(422);
    }

    public function test_reset_password_success(): void
    {
        $rider = $this->makeRider(['email' => 'reset2@pedalya.test']);

        DB::table('password_reset_tokens')->insert([
            'email' => 'reset2@pedalya.test',
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'reset2@pedalya.test',
            'code' => '123456',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertOk()->assertJsonPath('message', 'Password reset successfully.');

        $this->assertTrue(Hash::check('newpassword', $rider->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'reset2@pedalya.test']);
    }

    public function test_reset_password_no_record(): void
    {
        $rider = $this->makeRider(['email' => 'reset3@pedalya.test']);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'reset3@pedalya.test',
            'code' => '123456',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertStatus(422)->assertJsonPath('message', 'No password reset request was found.');
    }

    public function test_reset_password_expired_code(): void
    {
        $rider = $this->makeRider(['email' => 'reset4@pedalya.test']);

        DB::table('password_reset_tokens')->insert([
            'email' => 'reset4@pedalya.test',
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'reset4@pedalya.test',
            'code' => '123456',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertStatus(422)->assertJsonPath('message', 'The password reset code has expired.');
    }

    public function test_reset_password_wrong_code(): void
    {
        $rider = $this->makeRider(['email' => 'reset5@pedalya.test']);

        DB::table('password_reset_tokens')->insert([
            'email' => 'reset5@pedalya.test',
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'reset5@pedalya.test',
            'code' => '999999',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertStatus(422)->assertJsonPath('message', 'The password reset code is incorrect.');
    }

    public function test_reset_password_validation(): void
    {
        $this->postJson('/api/auth/reset-password', [
            'email' => 'bad',
            'code' => '12',
            'password' => 'short',
        ])->assertStatus(422);
    }
}
