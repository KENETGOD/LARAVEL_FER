<?php

namespace App\Contracts\Services;

use App\Models\Etiqueta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EtiquetaServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getById(int $id): ?Etiqueta;

    public function create(array $data): Etiqueta;

    public function update(int $id, array $data): ?Etiqueta;

    public function delete(int $id): bool;
}
