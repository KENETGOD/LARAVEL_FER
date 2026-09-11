<?php

namespace App\Services;

use App\Contracts\Repositories\PedidoRepositoryInterface;
use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Contracts\Services\PedidoServiceInterface;
use App\Enums\EstadoPedido;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoService implements PedidoServiceInterface
{
    private const TRANSICIONES = [
        'pendiente' => [EstadoPedido::PAGADO, EstadoPedido::CANCELADO],
        'pagado' => [EstadoPedido::ENVIADO, EstadoPedido::CANCELADO],
        'enviado' => [EstadoPedido::ENTREGADO],
        'entregado' => [],
        'cancelado' => [],
    ];

    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly ProductoRepositoryInterface $productoRepository
    ) {}

    public function paginateFor(User $user, int $perPage = 15): LengthAwarePaginator
    {
        if ($user->hasAnyRole(['admin', 'empleado'])) {
            return $this->pedidoRepository->paginateAll($perPage);
        }

        return $this->pedidoRepository->paginateByUser($user->id, $perPage);
    }

    public function getForUser(User $user, int $id): ?Pedido
    {
        $pedido = $this->pedidoRepository->find($id);

        if (! $pedido) {
            return null;
        }

        if (! $user->hasAnyRole(['admin', 'empleado']) && $pedido->user_id !== $user->id) {
            throw new AuthorizationException('No tienes permisos para ver este pedido');
        }

        return $pedido;
    }

    public function create(User $user, array $data): Pedido
    {
        $productos = $this->productoRepository->findMany(array_column($data['items'], 'producto_id'));

        if ($productos->count() !== count($data['items'])) {
            throw ValidationException::withMessages(['items' => 'Uno o más productos no existen']);
        }

        foreach ($productos as $producto) {
            if (! $producto->activo) {
                throw ValidationException::withMessages([
                    'items' => "El producto '{$producto->nombre}' no está disponible",
                ]);
            }
        }

        $total = 0;
        $attach = [];

        foreach ($data['items'] as $item) {
            $producto = $productos->firstWhere('id', $item['producto_id']);
            $precio = (float) $producto->precio;
            $cantidad = $item['cantidad'];

            $total += $precio * $cantidad;
            $attach[$producto->id] = [
                'cantidad' => $cantidad,
                'precio' => $precio,
            ];
        }

        return DB::transaction(function () use ($user, $total, $attach) {
            $pedido = $this->pedidoRepository->create([
                'user_id' => $user->id,
                'total' => $total,
                'estado' => EstadoPedido::PENDIENTE->value,
            ]);

            $this->pedidoRepository->attachProductos($pedido, $attach);

            return $this->pedidoRepository->find($pedido->id);
        });
    }

    public function updateEstado(int $id, EstadoPedido $estado): ?Pedido
    {
        $pedido = $this->pedidoRepository->find($id);

        if (! $pedido) {
            return null;
        }

        $actual = EstadoPedido::from($pedido->estado);

        if (! in_array($estado, self::TRANSICIONES[$actual->value], true)) {
            throw ValidationException::withMessages([
                'estado' => "No se puede pasar de '{$actual->label()}' a '{$estado->label()}'",
            ]);
        }

        return $this->pedidoRepository->updateEstado($pedido, $estado->value);
    }
}
