<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_allows_a_normal_request_and_then_returns_too_many_requests(): void
    {
        $user = User::create([
            'name' => 'Rate Limit User',
            'email' => 'rate-login@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertStatus(200);

        foreach (range(1, 5) as $attempt) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_forgot_password_returns_generic_success_then_is_throttled(): void
    {
        Notification::fake();
        $email = 'rate-forgot@example.com';
        User::create([
            'name' => 'Rate Limit User',
            'email' => $email,
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/api/auth/forgot-password', ['email' => $email])
            ->assertStatus(200)
            ->assertJsonPath('mensaje', 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.');

        foreach (range(1, 5) as $attempt) {
            $response = $this->postJson('/api/auth/forgot-password', ['email' => $email]);
        }

        $response->assertStatus(429);
    }
}
