<?php

namespace Database\Seeders;

use App\Enums\EstadoPedido;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->crearClientesDemo();

        $categorias = $this->crearCategorias();
        $etiquetas = $this->crearEtiquetas();
        $productos = $this->crearProductos($categorias);

        $this->asignarEtiquetas($productos, $etiquetas);

        if (Pedido::count() === 0) {
            $this->crearPedidos();
        }
    }

    private function crearClientesDemo(): void
    {
        $clientes = [
            'maria@example.com' => 'María García',
            'carlos@example.com' => 'Carlos López',
            'ana@example.com' => 'Ana Martínez',
        ];

        foreach ($clientes as $email => $nombre) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nombre, 'password' => Hash::make('password')]
            );

            $user->assignRole('cliente');
        }
    }

    private function crearCategorias(): array
    {
        $categorias = [
            'Tecnología' => 'Equipos y accesorios electrónicos',
            'Hogar' => 'Artículos para el hogar y decoración',
            'Deportes' => 'Equipamiento deportivo',
            'Moda' => 'Ropa y accesorios de moda',
            'Libros' => 'Libros y material de lectura',
            'Juguetes' => 'Juguetes y entretenimiento infantil',
        ];

        $creadas = [];

        foreach ($categorias as $nombre => $descripcion) {
            $creadas[$nombre] = Categoria::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }

        return $creadas;
    }

    private function crearEtiquetas(): array
    {
        $nombres = ['Oferta', 'Nuevo', 'Popular', 'Envío gratis', 'Edición limitada'];

        $creadas = [];

        foreach ($nombres as $nombre) {
            $creadas[$nombre] = Etiqueta::firstOrCreate(['nombre' => $nombre]);
        }

        return $creadas;
    }

    private function crearProductos(array $categorias): array
    {
        $productos = [
            'Tecnología' => [
                ['Teclado Mecánico RGB', 'Teclado con switches rojos y retroiluminación personalizable', 1299.99, true],
                ['Mouse Inalámbrico', 'Mouse ergonómico de 1600 DPI con conexión USB', 499.00, true],
                ['Audífonos Bluetooth', 'Audífonos over-ear con cancelación de ruido activa', 899.50, true],
                ['Monitor 24" Full HD', 'Monitor IPS con 75 Hz y puertos HDMI y DisplayPort', 3499.00, true],
                ['Disco SSD 1TB', 'Unidad de estado sólido NVMe con lectura de 3500 MB/s', 2199.00, true],
            ],
            'Hogar' => [
                ['Lámpara de Escritorio LED', 'Luz regulable con temperatura de color ajustable', 449.90, true],
                ['Cafetera Programable', 'Cafetera de 12 tazas con temporizador y jarra de vidrio', 1899.00, true],
                ['Juego de Sábanas Queen', 'Algodón suave de 300 hilos, incluye cubrecama', 999.00, true],
                ['Licuadora de Alto Rendimiento', 'Motor de 1000 W con vaso reforzado de 2 litros', 1699.50, true],
                ['Horno de Microondas', 'Capacidad de 25 litros con 10 niveles de potencia', 2499.00, true],
            ],
            'Deportes' => [
                ['Bicicleta de Montaña', 'Rodada 29 con frenos de disco y 21 velocidades', 7999.00, true],
                ['Pelota de Fútbol', 'Talla 5, costura termo-sellada para uso profesional', 349.90, true],
                ['Pesas Rústicas 2x10kg', 'Par de pesas hexagonales con recubrimiento antideslizante', 899.00, true],
                ['Cuerda para Saltar', 'Longitud ajustable con rodamientos de alta velocidad', 149.00, true],
                ['Yoga Mat Antideslizante', 'Almohadilla de 6 mm con correa de transporte', 399.00, true],
            ],
            'Moda' => [
                ['Camiseta Algodón Premium', 'Algodón peinado de 180 g, corte regular', 299.00, true],
                ['Jeans Skinny', 'Mezclilla elástica con lavado medio', 649.00, true],
                ['Tenis Urbanos', 'Suela de goma ligera y parte superior de tela', 1499.00, true],
                ['Gorra Deportiva', 'Ajuste con velcro y tejido transpirable', 249.00, true],
            ],
            'Libros' => [
                ['Aprendiendo Laravel 11', 'Guía práctica para crear APIs con Laravel y Eloquent', 599.00, true],
                ['PHP Moderno en la Práctica', 'Buenas prácticas, testing y arquitectura limpia en PHP', 449.00, true],
                ['Novela "El Gran Diseño"', 'Edición de tapa blanda con prólogo del autor', 299.00, true],
                ['Cuaderno de Programación', 'Cuaderno punteado ideal para apuntes de código', 199.00, true],
            ],
            'Juguetes' => [
                ['Set de Bloques 500 piezas', 'Bloques de plástico compatible con otras marcas', 799.00, true],
                ['Muñeca Interactiva', 'Responde al tacto con sonidos y canciones', 699.00, true],
                ['Carro a Control Remoto', 'Escala 1:16 con batería recargable y luces LED', 1199.00, true],
                ['Rompecabezas 1000 piezas', 'Imagen de paisaje con acabado satinado', 349.00, true],
            ],
        ];

        $creados = [];

        foreach ($productos as $categoria => $items) {
            foreach ($items as [$nombre, $descripcion, $precio, $activo]) {
                $creados[] = Producto::create([
                    'categoria_id' => $categorias[$categoria]->id,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'activo' => $activo,
                ]);
            }
        }

        return $creados;
    }

    private function asignarEtiquetas(array $productos, array $etiquetas): void
    {
        $nombres = array_keys($etiquetas);

        foreach ($productos as $producto) {
            $seleccion = collect($nombres)->random(rand(1, 3));

            $producto->etiquetas()->sync(
                collect($seleccion)->map(fn (string $nombre) => $etiquetas[$nombre]->id)
            );
        }
    }

    private function crearPedidos(): void
    {
        $clientes = User::role('cliente')->get();

        if ($clientes->isEmpty()) {
            return;
        }

        $productos = Producto::all();

        for ($i = 0; $i < 15; $i++) {
            $items = $productos->random(rand(1, 4));
            $estado = EstadoPedido::cases()[array_rand(EstadoPedido::cases())]->value;

            $pedido = Pedido::create([
                'user_id' => $clientes->random()->id,
                'total' => 0,
                'estado' => $estado,
            ]);

            $total = 0;

            foreach ($items as $producto) {
                $cantidad = rand(1, 3);

                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $cantidad,
                    'precio' => $producto->precio,
                ]);

                $total += $producto->precio * $cantidad;
            }

            $pedido->update(['total' => $total]);
        }
    }
}
