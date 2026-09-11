<?php

namespace App\Services;

use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Contracts\Services\ProductoServiceInterface;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ProductoService implements ProductoServiceInterface
{
    public function __construct(
        private readonly ProductoRepositoryInterface $productoRepository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productoRepository->paginate($perPage);
    }

    public function getById(int $id): ?Producto
    {
        return $this->productoRepository->find($id);
    }

    public function create(array $data): Producto
    {
        $etiquetasIds = $data['etiquetas_ids'] ?? [];
        unset($data['etiquetas_ids']);

        $producto = $this->productoRepository->create($data);

        if (! empty($etiquetasIds)) {
            $this->productoRepository->syncEtiquetas($producto, $etiquetasIds);
        }

        return $this->productoRepository->withRelations($producto);
    }

    public function update(int $id, array $data): ?Producto
    {
        $producto = $this->productoRepository->find($id);

        if (! $producto) {
            return null;
        }

        $etiquetasIds = $data['etiquetas_ids'] ?? null;
        unset($data['etiquetas_ids']);

        $producto = $this->productoRepository->update($producto, $data);

        if ($etiquetasIds !== null) {
            $this->productoRepository->syncEtiquetas($producto, $etiquetasIds);
        }

        return $this->productoRepository->withRelations($producto);
    }

    public function delete(int $id): bool
    {
        $producto = $this->productoRepository->find($id);

        if (! $producto) {
            return false;
        }

        if ($this->productoRepository->existeEnPedidos($id)) {
            throw ValidationException::withMessages([
                'producto' => 'No se puede eliminar un producto que forma parte de pedidos; puedes marcarlo como inactivo',
            ]);
        }

        return $this->productoRepository->delete($producto);
    }
}
