<?php

namespace App\Support;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['error', 'message'],
    properties: [
        new OA\Property(property: 'error', type: 'string', example: 'NOT_FOUND'),
        new OA\Property(property: 'message', type: 'string', example: 'Recurso no encontrado'),
        new OA\Property(property: 'details', type: 'object', nullable: true, description: 'Detalles de errores de validación'),
    ]
)]
#[OA\Schema(
    schema: 'Paginado',
    required: ['estado', 'data'],
    properties: [
        new OA\Property(property: 'estado', type: 'string', example: 'exito'),
        new OA\Property(property: 'data', type: 'object', required: ['current_page', 'data', 'per_page', 'total'], properties: [
            new OA\Property(property: 'current_page', type: 'integer', example: 1),
            new OA\Property(property: 'data', type: 'array', description: 'Elementos de la página', items: new OA\Items(type: 'object')),
            new OA\Property(property: 'first_page_url', type: 'string', nullable: true),
            new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
            new OA\Property(property: 'last_page', type: 'integer', example: 1),
            new OA\Property(property: 'last_page_url', type: 'string', nullable: true),
            new OA\Property(property: 'links', type: 'array', items: new OA\Items(type: 'object')),
            new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
            new OA\Property(property: 'path', type: 'string', nullable: true),
            new OA\Property(property: 'per_page', type: 'integer', example: 15),
            new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
            new OA\Property(property: 'to', type: 'integer', nullable: true),
            new OA\Property(property: 'total', type: 'integer', example: 42),
        ]),
    ]
)]
class OpenApiSchemas {}
