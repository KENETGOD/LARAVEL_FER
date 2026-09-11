<?php

namespace App\Contracts\Services;

use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoriaServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getById(int $id): ?Categoria;

    public function create(array $data): Categoria;

    public function update(int $id, array $data): ?Categoria;

    public function delete(int $id): bool;
}
