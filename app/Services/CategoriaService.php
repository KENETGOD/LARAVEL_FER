<?php

namespace App\Services;

use App\Contracts\Repositories\CategoriaRepositoryInterface;
use App\Contracts\Services\CategoriaServiceInterface;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CategoriaService implements CategoriaServiceInterface
{
    public function __construct(
        private readonly CategoriaRepositoryInterface $categoriaRepository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoriaRepository->paginate($perPage);
    }

    public function getById(int $id): ?Categoria
    {
        return $this->categoriaRepository->find($id);
    }

    public function create(array $data): Categoria
    {
        return $this->categoriaRepository->create($data);
    }

    public function update(int $id, array $data): ?Categoria
    {
        $categoria = $this->categoriaRepository->find($id);

        if (! $categoria) {
            return null;
        }

        return $this->categoriaRepository->update($categoria, $data);
    }

    public function delete(int $id): bool
    {
        $categoria = $this->categoriaRepository->find($id);

        if (! $categoria) {
            return false;
        }

        if ($this->categoriaRepository->tieneProductos($id)) {
            throw ValidationException::withMessages([
                'categoria' => 'No se puede eliminar una categoría que tiene productos asociados',
            ]);
        }

        return $this->categoriaRepository->delete($categoria);
    }
}
