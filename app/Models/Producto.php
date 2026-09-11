<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Producto',
    required: ['id', 'nombre', 'precio', 'categoria_id'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Teclado Mecánico'),
        new OA\Property(property: 'descripcion', type: 'string', nullable: true, example: 'Teclado con switches rojos'),
        new OA\Property(property: 'precio', type: 'number', format: 'float', example: 1200.50),
        new OA\Property(property: 'activo', type: 'boolean', example: true),
        new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
        new OA\Property(property: 'categoria', ref: '#/components/schemas/Categoria'),
        new OA\Property(property: 'etiquetas', type: 'array', items: new OA\Items(ref: '#/components/schemas/Etiqueta')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'activo',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class); // Relación con la categoría
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto') // Relación con los pedidos a través de la tabla intermedia 'pedido_producto'
            ->withPivot('cantidad', 'precio')
            ->withTimestamps();
    }

    public function etiquetas() // Relación con las etiquetas a través de la tabla intermedia 'etiqueta_producto'
    {
        return $this->belongsToMany(Etiqueta::class);
    }
}
