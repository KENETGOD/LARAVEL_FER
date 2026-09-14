<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserApiErrorTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        Role::findOrCreate('admin', 'api');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $admin->assignRole('admin');

        return auth('api')->login($admin);
    }

    public function test_show_returns_user_not_found_error(): void
    {
        $response = $this->withToken($this->adminToken())
            ->getJson('/api/usuarios/999');

        $response->assertStatus(404)
            ->assertJsonPath('error', 'USER_NOT_FOUND')
            ->assertJsonPath('message', 'Usuario no encontrado');
    }

    public function test_update_returns_user_not_found_error(): void
    {
        $response = $this->withToken($this->adminToken())
            ->putJson('/api/usuarios/999', ['name' => 'Nuevo nombre']);

        $response->assertStatus(404)
            ->assertJsonPath('error', 'USER_NOT_FOUND')
            ->assertJsonPath('message', 'Usuario no encontrado');
    }

    public function test_update_allows_user_to_keep_own_email(): void
    {
        $token = $this->adminToken();
        $user = User::create([
            'name' => 'Usuario',
            'email' => 'usuario@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withToken($token)
            ->putJson('/api/usuarios/'.$user->id, ['email' => $user->email]);

        $response->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_update_rejects_email_used_by_another_user(): void
    {
        $token = $this->adminToken();
        $user = User::create([
            'name' => 'Usuario',
            'email' => 'usuario@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $otherUser = User::create([
            'name' => 'Otro usuario',
            'email' => 'otro@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withToken($token)
            ->putJson('/api/usuarios/'.$user->id, ['email' => $otherUser->email]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['message', 'error', 'details' => ['email']]);
    }

    public function test_destroy_returns_user_not_found_error(): void
    {
        $response = $this->withToken($this->adminToken())
            ->deleteJson('/api/usuarios/999');

        $response->assertStatus(404)
            ->assertJsonPath('error', 'USER_NOT_FOUND')
            ->assertJsonPath('message', 'Usuario no encontrado');
    }

    public function test_store_returns_validation_error(): void
    {
        $response = $this->withToken($this->adminToken())
            ->postJson('/api/usuarios', [
                'name' => '',
                'email' => 'correo-invalido',
                'password' => 'corta',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['message', 'error', 'details' => ['name', 'email', 'password']]);
    }

    public function test_unregistered_route_returns_not_found_error(): void
    {
        $response = $this->getJson('/api/no-existe');

        $response->assertStatus(404)
            ->assertJsonPath('error', 'NOT_FOUND')
            ->assertJsonPath('message', 'Recurso no encontrado');
    }

    public function test_method_not_allowed_returns_error(): void
    {
        $response = $this->putJson('/api/usuarios');

        $response->assertStatus(405)
            ->assertJsonPath('error', 'METHOD_NOT_ALLOWED')
            ->assertJsonPath('message', 'Método no permitido para esta ruta');
    }

    public function test_unexpected_api_exception_returns_generic_internal_server_error(): void
    {
        Route::get('/api/testing/unexpected-error', function () {
            throw new \RuntimeException('sensitive exception details');
        });

        $response = $this->getJson('/api/testing/unexpected-error');

        $response->assertStatus(500)
            ->assertJsonPath('error', 'INTERNAL_SERVER_ERROR')
            ->assertJsonPath('message', 'Ocurrió un error interno en el servidor')
            ->assertJsonMissing(['message' => 'sensitive exception details'])
            ->assertDontSee('sensitive exception details');
    }
}
