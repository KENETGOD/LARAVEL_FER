<?php

namespace App\Contracts\Repositories;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductoRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Producto;

    public function findMany(array $ids): Collection;

    public function create(array $data): Producto;

    public function update(Producto $producto, array $data): Producto;

    public function syncEtiquetas(Producto $producto, array $etiquetasIds): void;

    public function existeEnPedidos(int $productoId): bool;

    public function withRelations(Producto $producto): Producto;

    public function delete(Producto $producto): bool;
}
