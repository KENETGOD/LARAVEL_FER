<?php

namespace Tests\Unit;

use App\Contracts\Repositories\PedidoRepositoryInterface;
use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Enums\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\PedidoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class PedidoServiceTest extends TestCase
{
    public function test_customer_pagination_uses_their_repository_scope(): void
    {
        $pedidoRepository = Mockery::mock(PedidoRepositoryInterface::class);
        $productoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('hasAnyRole')->once()->with(['admin', 'empleado'])->andReturnFalse();
        $paginator = new LengthAwarePaginator([], 0, 10);
        $pedidoRepository->shouldReceive('paginateByUser')->once()->with(7, 10)->andReturn($paginator);

        $this->assertSame($paginator, (new PedidoService($pedidoRepository, $productoRepository))->paginateFor($user, 10));
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $pedidoRepository = Mockery::mock(PedidoRepositoryInterface::class);
        $productoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('hasAnyRole')->once()->with(['admin', 'empleado'])->andReturnFalse();
        $pedidoRepository->shouldReceive('find')->once()->with(12)->andReturn(new Pedido(['user_id' => 8]));

        $this->expectException(AuthorizationException::class);
        (new PedidoService($pedidoRepository, $productoRepository))->getForUser($user, 12);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $pedidoRepository = Mockery::mock(PedidoRepositoryInterface::class);
        $productoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $pedidoRepository->shouldReceive('find')->once()->with(12)->andReturn(new Pedido(['estado' => 'pendiente']));

        $this->expectException(ValidationException::class);
        (new PedidoService($pedidoRepository, $productoRepository))->updateEstado(12, EstadoPedido::ENTREGADO);
    }

    public function test_inactive_products_are_rejected_without_database_access(): void
    {
        $pedidoRepository = Mockery::mock(PedidoRepositoryInterface::class);
        $productoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $productoRepository->shouldReceive('findMany')->once()->with([3])->andReturn(new Collection([
            new Producto(['id' => 3, 'nombre' => 'Teclado', 'activo' => false]),
        ]));
        $user = new User(['id' => 7]);

        $this->expectException(ValidationException::class);
        (new PedidoService($pedidoRepository, $productoRepository))->create($user, [
            'items' => [['producto_id' => 3, 'cantidad' => 1]],
        ]);
    }
}
