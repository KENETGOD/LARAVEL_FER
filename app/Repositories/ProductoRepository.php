<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductoRepository implements ProductoRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Producto::with(['categoria', 'etiquetas'])->paginate($perPage);
    }

    public function find(int $id): ?Producto
    {
        return Producto::with(['categoria', 'etiquetas'])->find($id);
    }

    public function findMany(array $ids): Collection
    {
        return Producto::with(['categoria', 'etiquetas'])->whereIn('id', $ids)->get();
    }

    public function create(array $data): Producto
    {
        return Producto::create($data);
    }

    public function update(Producto $producto, array $data): Producto
    {
        $producto->update($data);

        return $producto;
    }

    public function syncEtiquetas(Producto $producto, array $etiquetasIds): void
    {
        $producto->etiquetas()->sync($etiquetasIds);
    }

    public function existeEnPedidos(int $productoId): bool
    {
        return Producto::whereKey($productoId)->whereHas('pedidos')->exists();
    }

    public function withRelations(Producto $producto): Producto
    {
        return $producto->load(['categoria', 'etiquetas']);
    }

    public function delete(Producto $producto): bool
    {
        return $producto->delete();
    }
}
