<?php

namespace App\Repositories;

use App\Contracts\Repositories\PedidoRepositoryInterface;
use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PedidoRepository implements PedidoRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with(['user', 'productos'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::with(['user', 'productos'])
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): ?Pedido
    {
        return Pedido::with(['user', 'productos'])->find($id);
    }

    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function attachProductos(Pedido $pedido, array $items): void
    {
        $pedido->productos()->attach($items);
    }

    public function updateEstado(Pedido $pedido, string $estado): Pedido
    {
        $pedido->update(['estado' => $estado]);

        return $pedido;
    }
}
