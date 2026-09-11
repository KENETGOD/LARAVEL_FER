<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Etiqueta',
    required: ['id', 'nombre'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Oferta'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class Etiqueta extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function productos() // Relación con los productos a través de la tabla intermedia 'etiqueta_producto'
    {
        return $this->belongsToMany(Producto::class);
    }
}
