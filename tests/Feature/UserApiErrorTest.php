<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
