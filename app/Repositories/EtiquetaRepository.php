<?php

namespace App\Repositories;

use App\Contracts\Repositories\EtiquetaRepositoryInterface;
use App\Models\Etiqueta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EtiquetaRepository implements EtiquetaRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Etiqueta::orderBy('nombre', 'asc')->paginate($perPage);
    }

    public function find(int $id): ?Etiqueta
    {
        return Etiqueta::find($id);
    }

    public function create(array $data): Etiqueta
    {
        return Etiqueta::create($data);
    }

    public function update(Etiqueta $etiqueta, array $data): Etiqueta
    {
        $etiqueta->update($data);

        return $etiqueta;
    }

    public function tieneProductos(int $etiquetaId): bool
    {
        return Etiqueta::whereKey($etiquetaId)->whereHas('productos')->exists();
    }

    public function delete(Etiqueta $etiqueta): bool
    {
        return $etiqueta->delete();
    }
}
