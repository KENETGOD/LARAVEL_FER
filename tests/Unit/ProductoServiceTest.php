<?php

namespace Tests\Unit;

use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ProductoServiceTest extends TestCase
{
    public function test_create_separates_and_syncs_tags_through_repository(): void
    {
        $repository = Mockery::mock(ProductoRepositoryInterface::class);
        $producto = new Producto(['nombre' => 'Teclado']);
        $repository->shouldReceive('create')->once()->with(['nombre' => 'Teclado'])->andReturn($producto);
        $repository->shouldReceive('syncEtiquetas')->once()->with($producto, [2, 4]);
        $repository->shouldReceive('withRelations')->once()->with($producto)->andReturn($producto);

        $this->assertSame($producto, (new ProductoService($repository))->create([
            'nombre' => 'Teclado',
            'etiquetas_ids' => [2, 4],
        ]));
    }

    public function test_delete_rejects_products_used_in_orders(): void
    {
        $repository = Mockery::mock(ProductoRepositoryInterface::class);
        $producto = new Producto;
        $producto->id = 3;
        $repository->shouldReceive('find')->once()->with(3)->andReturn($producto);
        $repository->shouldReceive('existeEnPedidos')->once()->with(3)->andReturnTrue();

        $this->expectException(ValidationException::class);
        (new ProductoService($repository))->delete($producto->id);
    }

    public function test_update_returns_null_when_product_does_not_exist(): void
    {
        $repository = Mockery::mock(ProductoRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->with(3)->andReturnNull();

        $this->assertNull((new ProductoService($repository))->update(3, ['nombre' => 'Nuevo']));
    }
}
