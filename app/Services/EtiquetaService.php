<?php

namespace App\Services;

use App\Contracts\Repositories\EtiquetaRepositoryInterface;
use App\Contracts\Services\EtiquetaServiceInterface;
use App\Models\Etiqueta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class EtiquetaService implements EtiquetaServiceInterface
{
    public function __construct(
        private readonly EtiquetaRepositoryInterface $etiquetaRepository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->etiquetaRepository->paginate($perPage);
    }

    public function getById(int $id): ?Etiqueta
    {
        return $this->etiquetaRepository->find($id);
    }

    public function create(array $data): Etiqueta
    {
        return $this->etiquetaRepository->create($data);
    }

    public function update(int $id, array $data): ?Etiqueta
    {
        $etiqueta = $this->etiquetaRepository->find($id);

        if (! $etiqueta) {
            return null;
        }

        return $this->etiquetaRepository->update($etiqueta, $data);
    }

    public function delete(int $id): bool
    {
        $etiqueta = $this->etiquetaRepository->find($id);

        if (! $etiqueta) {
            return false;
        }

        if ($this->etiquetaRepository->tieneProductos($id)) {
            throw ValidationException::withMessages([
                'etiqueta' => 'No se puede eliminar una etiqueta asignada a productos',
            ]);
        }

        return $this->etiquetaRepository->delete($etiqueta);
    }
}
