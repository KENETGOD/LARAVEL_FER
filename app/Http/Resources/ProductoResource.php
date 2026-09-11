<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductoResource',
    required: ['id', 'nombre', 'precio_formateado', 'disponible'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Teclado Mecánico'),
        new OA\Property(property: 'descripcion', type: 'string', nullable: true, example: 'Teclado con switches rojos'),
        new OA\Property(property: 'precio_formateado', type: 'number', format: 'float', example: 1200.50),
        new OA\Property(property: 'disponible', type: 'boolean', example: true),
        new OA\Property(property: 'categoria', type: 'string', nullable: true, example: 'Tecnología'),
        new OA\Property(
            property: 'etiquetas',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Oferta'),
                ]
            )
        ),
    ]
)]
class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio_formateado' => (float) $this->precio,
            'disponible' => (bool) $this->activo,

            'categoria' => $this->whenLoaded('categoria', function () { // Verifica si la relación 'categoria' está cargada
                return $this->categoria->nombre;
            }),

            'etiquetas' => $this->whenLoaded('etiquetas', fn () => $this->etiquetas->map(
                fn ($etiqueta) => [
                    'id' => $etiqueta->id,
                    'nombre' => $etiqueta->nombre,
                ]
            )->values()),
        ];
    }
}
