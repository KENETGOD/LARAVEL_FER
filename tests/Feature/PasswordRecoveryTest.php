<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/forgot-password', ['email' => $user->email]);

        $response->assertStatus(200);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_returns_same_message_for_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'nadie@example.com']);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.');
    }

    public function test_forgot_password_validates_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'no-es-un-email']);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_verify_reset_token_returns_valid(): void
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/verify-reset-token', [
            'email' => $user->email,
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'El enlace de restablecimiento es válido.');
    }

    public function test_verify_reset_token_rejects_invalid_token(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/verify-reset-token', [
            'email' => $user->email,
            'token' => 'token-invalido',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error', 'INVALID_TOKEN');
    }

    public function test_verify_reset_token_returns_user_not_found(): void
    {
        $response = $this->postJson('/api/auth/verify-reset-token', [
            'email' => 'nadie@example.com',
            'token' => 'cualquiera',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('error', 'USER_NOT_FOUND');
    }

    public function test_reset_password_changes_password(): void
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('mensaje', 'Contraseña restablecida con éxito.');
        $this->assertTrue(Hash::check('nueva123', $user->fresh()->password));
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => 'token-invalido',
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error', 'INVALID_TOKEN');
    }

    public function test_reset_password_rejects_expired_token(): void
    {
        $user = $this->createUser();

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('token-viejo'),
            'created_at' => now()->subMinutes(90),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => 'token-viejo',
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error', 'INVALID_TOKEN');
    }

    public function test_reset_password_requires_confirmation(): void
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nueva123',
            'password_confirmation' => 'otra123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_refresh_returns_new_token(): void
    {
        $user = $this->createUser();
        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure(['authorization' => ['token', 'type']])
            ->assertJsonPath('authorization.type', 'bearer');
    }

    public function test_refresh_without_token_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401)
            ->assertJsonPath('error', 'UNAUTHORIZED');
    }

    public function test_refresh_blacklists_previous_token(): void
    {
        $user = $this->createUser();
        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)->postJson('/api/auth/refresh');
        $response->assertStatus(200);

        $oldToken = $this->withToken($token)->postJson('/api/auth/logout');

        $oldToken->assertStatus(401)
            ->assertJsonPath('error', 'UNAUTHORIZED');
    }
}
