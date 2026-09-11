<?php

namespace App\Contracts\Repositories;

use App\Models\Etiqueta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EtiquetaRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Etiqueta;

    public function create(array $data): Etiqueta;

    public function update(Etiqueta $etiqueta, array $data): Etiqueta;

    public function tieneProductos(int $etiquetaId): bool;

    public function delete(Etiqueta $etiqueta): bool;
}
