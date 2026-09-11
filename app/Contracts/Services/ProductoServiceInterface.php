<?php

namespace App\Contracts\Services;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductoServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getById(int $id): ?Producto;

    public function create(array $data): Producto;

    public function update(int $id, array $data): ?Producto;

    public function delete(int $id): bool;
}
