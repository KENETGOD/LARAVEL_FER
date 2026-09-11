<?php

namespace App\Contracts\Repositories;

use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PedidoRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function paginateAll(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Pedido;

    public function create(array $data): Pedido;

    public function attachProductos(Pedido $pedido, array $items): void;

    public function updateEstado(Pedido $pedido, string $estado): Pedido;
}
