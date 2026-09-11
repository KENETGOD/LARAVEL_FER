<?php

namespace App\Repositories;

use App\Contracts\Repositories\CategoriaRepositoryInterface;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoriaRepository implements CategoriaRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Categoria::orderBy('nombre', 'asc')->paginate($perPage);
    }

    public function find(int $id): ?Categoria
    {
        return Categoria::find($id);
    }

    public function create(array $data): Categoria
    {
        return Categoria::create($data);
    }

    public function update(Categoria $categoria, array $data): Categoria
    {
        $categoria->update($data);

        return $categoria;
    }

    public function tieneProductos(int $categoriaId): bool
    {
        return Categoria::whereKey($categoriaId)->whereHas('productos')->exists();
    }

    public function delete(Categoria $categoria): bool
    {
        return $categoria->delete();
    }
}
