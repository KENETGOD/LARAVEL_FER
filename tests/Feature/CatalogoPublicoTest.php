<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogoPublicoTest extends TestCase
{
    use RefreshDatabase;

    private function crearRol(string $rol): void
    {
        Role::findOrCreate($rol, 'api');
    }

    private function crearUsuario(string $rol, string $email): User
    {
        $this->crearRol($rol);

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

    private function crearCategoria(): Categoria
    {
        return Categoria::create(['nombre' => 'Tecnología']);
    }

    private function crearEtiqueta(): Etiqueta
    {
        return Etiqueta::create(['nombre' => 'Oferta']);
    }

    private function crearProducto(int $categoriaId, bool $activo = true): Producto
    {
        return Producto::create([
            'categoria_id' => $categoriaId,
            'nombre' => 'Teclado Mecánico',
            'precio' => 1200.50,
            'activo' => $activo,
        ]);
    }

    public function test_cualquiera_puede_listar_productos_con_categoria_y_etiquetas(): void
    {
        $categoria = $this->crearCategoria();
        $producto = $this->crearProducto($categoria->id);
        $etiqueta = $this->crearEtiqueta();
        $producto->etiquetas()->attach($etiqueta->id);

        $this->getJson('/api/productos')
            ->assertStatus(200)
            ->assertJsonPath('estado', 'exito')
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.categoria', 'Tecnología')
            ->assertJsonPath('data.data.0.etiquetas.0.nombre', 'Oferta');
    }

    public function test_cualquiera_puede_ver_un_producto_sin_token(): void
    {
        $categoria = $this->crearCategoria();
        $producto = $this->crearProducto($categoria->id);

        $this->getJson("/api/productos/{$producto->id}")
            ->assertStatus(200)
            ->assertJsonPath('categoria', 'Tecnología');
    }

    public function test_sin_token_no_puede_crear_producto(): void
    {
        $categoria = $this->crearCategoria();

        $this->postJson('/api/productos', [
            'categoria_id' => $categoria->id,
            'nombre' => 'Mouse',
            'precio' => 300,
        ])->assertStatus(401);
    }

    public function test_cliente_no_puede_gestionar_productos(): void
    {
        $cliente = $this->crearUsuario('cliente', 'cliente@example.com');
        $categoria = $this->crearCategoria();

        $this->withToken($this->token($cliente))
            ->postJson('/api/productos', [
                'categoria_id' => $categoria->id,
                'nombre' => 'Mouse',
                'precio' => 300,
            ])
            ->assertStatus(403);
    }

    public function test_empleado_puede_crear_producto_y_asignar_etiquetas(): void
    {
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');
        $categoria = $this->crearCategoria();
        $etiqueta = $this->crearEtiqueta();

        $response = $this->withToken($this->token($empleado))
            ->postJson('/api/productos', [
                'categoria_id' => $categoria->id,
                'nombre' => 'Mouse Gamer',
                'precio' => 500,
                'etiquetas_ids' => [$etiqueta->id],
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['mensaje', 'producto' => ['id', 'nombre', 'precio', 'categoria_id']])
            ->assertJsonPath('mensaje', 'Producto creado con éxito');

        $id = $response->json('producto.id');

        $this->assertDatabaseHas('etiqueta_producto', [
            'producto_id' => $id,
            'etiqueta_id' => $etiqueta->id,
        ]);
    }

    public function test_empleado_puede_editar_etiquetas_de_un_producto(): void
    {
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');
        $categoria = $this->crearCategoria();
        $etiqueta = $this->crearEtiqueta();
        $producto = $this->crearProducto($categoria->id);

        $this->withToken($this->token($empleado))
            ->putJson("/api/productos/{$producto->id}", [
                'etiquetas_ids' => [$etiqueta->id],
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.etiquetas.0.nombre', 'Oferta');
    }

    public function test_empleado_no_puede_eliminar_producto(): void
    {
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');
        $categoria = $this->crearCategoria();
        $producto = $this->crearProducto($categoria->id);

        $this->withToken($this->token($empleado))
            ->deleteJson("/api/productos/{$producto->id}")
            ->assertStatus(403);
    }

    public function test_admin_puede_eliminar_producto(): void
    {
        $admin = $this->crearUsuario('admin', 'admin@example.com');
        $categoria = $this->crearCategoria();
        $producto = $this->crearProducto($categoria->id);

        $this->withToken($this->token($admin))
            ->deleteJson("/api/productos/{$producto->id}")
            ->assertStatus(200)
            ->assertJsonPath('mensaje', 'Producto eliminado correctamente');

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }

    public function test_admin_no_puede_eliminar_categoria_con_productos(): void
    {
        $admin = $this->crearUsuario('admin', 'admin@example.com');
        $categoria = $this->crearCategoria();
        $this->crearProducto($categoria->id);

        $this->withToken($this->token($admin))
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_admin_no_puede_eliminar_producto_que_esta_en_pedidos(): void
    {
        $admin = $this->crearUsuario('admin', 'admin@example.com');
        $categoria = $this->crearCategoria();
        $producto = $this->crearProducto($categoria->id);

        $pedido = Pedido::create([
            'user_id' => $admin->id,
            'total' => 1200.50,
            'estado' => 'pendiente',
        ]);
        $pedido->productos()->attach($producto->id, [
            'cantidad' => 1,
            'precio' => 1200.50,
        ]);

        $this->withToken($this->token($admin))
            ->deleteJson("/api/productos/{$producto->id}")
            ->assertStatus(422)
            ->assertJsonPath('error', 'VALIDATION_ERROR');
    }

    public function test_empleado_no_puede_eliminar_categoria(): void
    {
        $empleado = $this->crearUsuario('empleado', 'empleado@example.com');
        $categoria = $this->crearCategoria();

        $this->withToken($this->token($empleado))
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertStatus(403);
    }
}
