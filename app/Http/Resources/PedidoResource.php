<?php

namespace App\Http\Resources;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PedidoResource',
    required: ['id', 'user_id', 'total', 'estado'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 2500.00),
        new OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'], example: 'pendiente'),
        new OA\Property(property: 'usuario', type: 'string', nullable: true, example: 'Juan Pérez'),
        new OA\Property(
            property: 'productos',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Teclado Mecánico'),
                    new OA\Property(property: 'cantidad', type: 'integer', example: 2),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', example: 1200.50),
                    new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 2401.00),
                ]
            )
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total' => (float) $this->total,
            'estado' => $this->estado,
            'usuario' => $this->whenLoaded('user', fn () => $this->user?->name),
            'productos' => $this->whenLoaded('productos', fn () => $this->productos->map(
                fn (Producto $producto) => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => (float) $producto->pivot->precio,
                    'subtotal' => (float) ($producto->pivot->cantidad * $producto->pivot->precio),
                ]
            )->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
