<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PedidoApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol, string $email): User
    {
        Role::findOrCreate($rol, 'api');

        $user = User::create([
            'name' => ucfirst($rol),
            'email' => $email,
            'password' => bcrypt('secret123'),
        ]);

        $user->assignRole($rol);

        return $user;
    }

    private function token(User $user): string
    {
        return auth('api')->login($user);
    }

    private function crearProducto(bool $activo = true): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Tecnología']);

        return Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Teclado Mecánico',
            'precio' => 1200.50,
            'activo' => $activo,
        ]);
    }

    public function test_sin_token_no_puede_listar_pedidos(): void
    {
        $this->getJson('/api/pedidos')->assertStatus(401);
    }

    public function test_cliente_crea_pedido_con_total_y_estado_pendiente(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $producto = $this->crearProducto();

        $this->withToken($this->token($cliente))
            ->postJson('/api/pedidos', [
                'items' => [
                    ['producto_id' => $producto->id, 'cantidad' => 2],
                ],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.total', 2401)
            ->assertJsonPath('data.productos.0.cantidad', 2)
            ->assertJsonPath('data.productos.0.precio', 1200.5)
            ->assertJsonPath('data.productos.0.subtotal', 2401);
    }

    public function test_cliente_crea_pedido_con_multiples_productos_y_cantidades(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $primerProducto = $this->crearProducto();
        $segundoProducto = $this->crearProducto();

        $response = $this->withToken($this->token($cliente))
            ->postJson('/api/pedidos', [
                'items' => [
                    ['producto_id' => $primerProducto->id, 'cantidad' => 2],
                    ['producto_id' => $segundoProducto->id, 'cantidad' => 3],
                ],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.total', 6002.5);

        $this->assertCount(2, $response->json('data.productos'));
        $this->assertDatabaseHas('pedido_producto', [
            'producto_id' => $primerProducto->id,
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('pedido_producto', [
            'producto_id' => $segundoProducto->id,
            'cantidad' => 3,
        ]);
    }

    public function test_cliente_solo_ve_sus_pedidos(): void
    {
        $cliente1 = $this->crearUsuario('cliente', 'cliente1@example.com');
        $cliente2 = $this->crearUsuario('cliente', 'cliente2@example.com');

        $pedido1 = Pedido::create(['user_id' => $cliente1->id, 'total' => 100, 'estado' => 'pendiente']);
        Pedido::create(['user_id' => $cliente2->id, 'total' => 200, 'estado' => 'pendiente']);

        $this->withToken($this->token($cliente1))
            ->getJson('/api/pedidos')
            ->assertStatus(200)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $pedido1->id);
    }

    public function test_cliente_no_puede_ver_pedido_de_otro_usuario(): void
    {
        $cliente1 = $this->crearUsuario('cliente', 'cliente1@example.com');
        $cliente2 = $this->crearUsuario('cliente', 'cliente2@example.com');

        $pedido2 = Pedido::create(['user_id' => $cliente2->id, 'total' => 200, 'estado' => 'pendiente']);

        $this->withToken($this->token($cliente1))
            ->getJson("/api/pedidos/{$pedido2->id}")
            ->assertStatus(403)
            ->assertJsonPath('error', 'FORBIDDEN');
    }

    public function test_empleado_ve_todos_los_pedidos(): void
    {
        $cliente1 = $this->crearUsuario('cliente', 'cliente1@example.com');
        $cliente2 = $this->crearUsuario('cliente', 'cliente2@example.com');
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');

        Pedido::create(['user_id' => $cliente1->id, 'total' => 100, 'estado' => 'pendiente']);
        Pedido::create(['user_id' => $cliente2->id, 'total' => 200, 'estado' => 'pendiente']);

        $this->withToken($this->token($empleado))
            ->getJson('/api/pedidos')
            ->assertStatus(200)
            ->assertJsonPath('data.total', 2);
    }

    public function test_admin_ve_todos_los_pedidos(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $admin = $this->crearUsuario('admin', 'admin@example.com');

        Pedido::create(['user_id' => $cliente->id, 'total' => 100, 'estado' => 'pendiente']);
        Pedido::create(['user_id' => $cliente->id, 'total' => 200, 'estado' => 'pendiente']);

        $this->withToken($this->token($admin))
            ->getJson('/api/pedidos')
            ->assertStatus(200)
            ->assertJsonPath('data.total', 2);
    }

    public function test_empleado_no_puede_cambiar_estado(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');

        $pedido = Pedido::create(['user_id' => $cliente->id, 'total' => 100, 'estado' => 'pendiente']);

        $this->withToken($this->token($empleado))
            ->putJson("/api/pedidos/{$pedido->id}/estado", ['estado' => 'pagado'])
            ->assertStatus(403);
    }

    public function test_admin_cambia_estado_respetando_transiciones(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $admin = $this->crearUsuario('admin', 'admin@example.com');

        $pedido = Pedido::create(['user_id' => $cliente->id, 'total' => 100, 'estado' => 'pendiente']);

        $this->withToken($this->token($admin))
            ->putJson("/api/pedidos/{$pedido->id}/estado", ['estado' => 'pagado'])
            ->assertStatus(200)
            ->assertJsonPath('data.estado', 'pagado');

        $this->withToken($this->token($admin))
            ->putJson("/api/pedidos/{$pedido->id}/estado", ['estado' => 'enviado'])
            ->assertStatus(200);

        $this->withToken($this->token($admin))
            ->putJson("/api/pedidos/{$pedido->id}/estado", ['estado' => 'entregado'])
            ->assertStatus(200)
            ->assertJsonPath('data.estado', 'entregado');
    }

    public function test_admin_no_puede_saltarse_transiciones(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $admin = $this->crearUsuario('admin', 'admin@example.com');

        $pedido = Pedido::create(['user_id' => $cliente->id, 'total' => 100, 'estado' => 'pendiente']);

        $this->withToken($this->token($admin))
            ->putJson("/api/pedidos/{$pedido->id}/estado", ['estado' => 'entregado'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_no_se_puede_pedir_producto_inactivo(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $producto = $this->crearProducto(activo: false);

        $this->withToken($this->token($cliente))
            ->postJson('/api/pedidos', [
                'items' => [
                    ['producto_id' => $producto->id, 'cantidad' => 1],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_no_se_puede_pedir_producto_inexistente(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');

        $this->withToken($this->token($cliente))
            ->postJson('/api/pedidos', [
                'items' => [
                    ['producto_id' => 9999, 'cantidad' => 1],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }
}
