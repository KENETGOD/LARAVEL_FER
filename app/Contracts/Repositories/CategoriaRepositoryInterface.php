<?php

namespace App\Contracts\Repositories;

use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoriaRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Categoria;

    public function create(array $data): Categoria;

    public function update(Categoria $categoria, array $data): Categoria;

    public function tieneProductos(int $categoriaId): bool;

    public function delete(Categoria $categoria): bool;
}
