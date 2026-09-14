<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiContractCoverageTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        Role::findOrCreate($role, 'api');

        $user = User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'secret123',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function token(string $role = 'admin', string $email = 'admin@example.com'): string
    {
        return auth('api')->login($this->user($role, $email));
    }

    public function test_authentication_contract_covers_register_login_logout_and_refresh(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Nuevo usuario',
            'email' => 'nuevo@example.com',
            'password' => 'secret123',
        ]);

        $register->assertCreated()
            ->assertJsonStructure([
                'mensaje',
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'authorization' => ['token', 'type'],
            ])
            ->assertJsonPath('authorization.type', 'bearer')
            ->assertJsonMissingPath('user.password');

        $login = $this->postJson('/api/auth/login', [
            'email' => 'nuevo@example.com',
            'password' => 'secret123',
        ]);

        $login->assertOk()
            ->assertJsonStructure(['mensaje', 'user' => ['id', 'name', 'email'], 'authorization' => ['token', 'type']])
            ->assertJsonPath('user.email', 'nuevo@example.com')
            ->assertJsonPath('authorization.type', 'bearer');

        $token = $login->json('authorization.token');
        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('mensaje', 'Sesión cerrada exitosamente');

        $refreshToken = auth('api')->login(User::where('email', 'nuevo@example.com')->firstOrFail());
        $this->withToken($refreshToken)
            ->postJson('/api/auth/refresh')
            ->assertOk()
            ->assertJsonPath('mensaje', 'Token renovado con éxito')
            ->assertJsonStructure(['authorization' => ['token', 'type']])
            ->assertJsonPath('authorization.type', 'bearer');
    }

    public function test_authentication_validation_and_credentials_errors_use_contract_envelopes(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['message', 'error', 'details' => ['name', 'email', 'password']]);

        $this->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'secret123',
        ])->assertStatus(401)
            ->assertJsonPath('error', 'UNAUTHORIZED')
            ->assertJsonPath('message', 'Credenciales incorrectas');
    }

    public function test_category_crud_covers_employee_access_pagination_resources_and_admin_delete(): void
    {
        $employeeToken = $this->token('empleado', 'empleado@example.com');
        $created = $this->withToken($employeeToken)
            ->postJson('/api/categorias', ['nombre' => 'Tecnología'])
            ->assertCreated()
            ->assertJsonStructure(['mensaje', 'data' => ['id', 'nombre', 'created_at', 'updated_at']]);
        $categoryId = $created->json('data.id');
        Categoria::create(['nombre' => 'Hogar']);

        $this->withToken($employeeToken)
            ->getJson('/api/categorias?per_page=1')
            ->assertOk()
            ->assertJsonPath('estado', 'exito')
            ->assertJsonPath('data.per_page', 1)
            ->assertJsonPath('data.total', 2)
            ->assertJsonStructure(['data' => ['current_page', 'data' => [['id', 'nombre']], 'last_page', 'total']]);

        $this->withToken($employeeToken)
            ->getJson('/api/categorias/'.$categoryId)
            ->assertOk()
            ->assertJsonPath('id', $categoryId)
            ->assertJsonPath('nombre', 'Tecnología');

        $this->withToken($employeeToken)
            ->putJson('/api/categorias/'.$categoryId, ['nombre' => 'Tecnología actualizada'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Tecnología actualizada');

        $this->withToken($this->token('cliente', 'cliente@example.com'))
            ->getJson('/api/categorias')
            ->assertStatus(403)
            ->assertJsonPath('error', 'FORBIDDEN');

        $this->withToken($this->token('admin', 'admin@example.com'))
            ->deleteJson('/api/categorias/'.$categoryId)
            ->assertOk()
            ->assertJsonPath('mensaje', 'Categoría eliminada');
    }

    public function test_tag_crud_covers_patch_validation_and_admin_delete(): void
    {
        $employeeToken = $this->token('empleado', 'empleado@example.com');

        $this->withToken($employeeToken)
            ->postJson('/api/etiquetas', ['nombre' => 'Oferta'])
            ->assertCreated()
            ->assertJsonStructure(['mensaje', 'data' => ['id', 'nombre', 'created_at', 'updated_at']]);
        $tag = Etiqueta::firstOrFail();

        $this->withToken($employeeToken)
            ->getJson('/api/etiquetas?per_page=1')
            ->assertOk()
            ->assertJsonPath('estado', 'exito')
            ->assertJsonPath('data.data.0.nombre', 'Oferta')
            ->assertJsonPath('data.per_page', 1);

        $this->withToken($employeeToken)
            ->patchJson('/api/etiquetas/'.$tag->id, ['nombre' => 'Promoción'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Promoción');

        $this->withToken($employeeToken)
            ->postJson('/api/etiquetas', ['nombre' => ''])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['details' => ['nombre']]);

        $this->withToken($this->token('admin', 'admin@example.com'))
            ->deleteJson('/api/etiquetas/'.$tag->id)
            ->assertOk()
            ->assertJsonPath('mensaje', 'Etiqueta eliminada');
    }

    public function test_product_contract_covers_public_pagination_and_validation_envelope(): void
    {
        $category = Categoria::create(['nombre' => 'Tecnología']);
        Producto::create(['categoria_id' => $category->id, 'nombre' => 'Mouse', 'precio' => 300, 'activo' => true]);
        Producto::create(['categoria_id' => $category->id, 'nombre' => 'Teclado', 'precio' => 700, 'activo' => true]);

        $this->getJson('/api/productos?per_page=1')
            ->assertOk()
            ->assertJsonPath('estado', 'exito')
            ->assertJsonPath('data.per_page', 1)
            ->assertJsonPath('data.total', 2)
            ->assertJsonStructure(['data' => ['data' => [['id', 'nombre', 'precio_formateado', 'disponible', 'categoria', 'etiquetas']]]]);

        $this->withToken($this->token('empleado', 'empleado@example.com'))
            ->postJson('/api/productos', ['nombre' => '', 'precio' => -1, 'categoria_id' => 999])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['details' => ['nombre', 'precio', 'categoria_id']]);
    }

    public function test_user_crud_covers_admin_only_access_pagination_resources_and_delete(): void
    {
        $adminToken = $this->token('admin', 'admin@example.com');
        $created = $this->withToken($adminToken)
            ->postJson('/api/usuarios', [
                'name' => 'Operador',
                'email' => 'operador@example.com',
                'password' => 'secret123',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJsonMissingPath('password');
        $userId = $created->json('id');

        $this->withToken($adminToken)
            ->getJson('/api/usuarios?per_page=1')
            ->assertOk()
            ->assertJsonPath('estado', 'exito')
            ->assertJsonPath('data.per_page', 1)
            ->assertJsonStructure(['data' => ['data' => [['id', 'name', 'email']], 'total']]);

        $this->withToken($adminToken)
            ->getJson('/api/usuarios/'.$userId)
            ->assertOk()
            ->assertJsonPath('id', $userId)
            ->assertJsonPath('email', 'operador@example.com');

        $this->withToken($adminToken)
            ->patchJson('/api/usuarios/'.$userId, ['name' => 'Operador actualizado'])
            ->assertOk()
            ->assertJsonPath('name', 'Operador actualizado');

        $this->withToken($this->token('empleado', 'empleado@example.com'))
            ->getJson('/api/usuarios')
            ->assertStatus(403)
            ->assertJsonPath('error', 'FORBIDDEN');

        $this->withToken($this->token('admin', 'delete-admin@example.com'))
            ->deleteJson('/api/usuarios/'.$userId)
            ->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_store_validation_and_password_are_protected(): void
    {
        $this->withToken($this->token())
            ->postJson('/api/usuarios', [
                'name' => 'Nuevo usuario',
                'email' => 'protected@example.com',
                'password' => 'secret123',
            ])
            ->assertCreated();

        $this->assertTrue(Hash::check('secret123', User::where('email', 'protected@example.com')->firstOrFail()->password));

        $this->withToken($this->token('admin', 'second-admin@example.com'))
            ->postJson('/api/usuarios', ['name' => '', 'email' => 'bad', 'password' => 'short'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR')
            ->assertJsonStructure(['details' => ['name', 'email', 'password']]);
    }
}
