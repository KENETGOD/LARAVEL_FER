<?php

namespace App\Contracts\Services;

use App\Enums\EstadoPedido;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PedidoServiceInterface
{
    public function paginateFor(User $user, int $perPage = 15): LengthAwarePaginator;

    public function getForUser(User $user, int $id): ?Pedido;

    public function create(User $user, array $data): Pedido;

    public function updateEstado(int $id, EstadoPedido $estado): ?Pedido;
}
