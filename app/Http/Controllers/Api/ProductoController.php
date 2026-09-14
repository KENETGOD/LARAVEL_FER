<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ProductoServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Support\ApiConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoServiceInterface $productoService
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/productos',
        summary: 'Listar productos',
        description: 'Público. Devuelve los productos paginados con su categoría y etiquetas.',
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Cantidad de registros por página',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado paginado de productos',
                content: new OA\JsonContent(ref: '#/components/schemas/Paginado')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        return json_paginado(
            $this->productoService->paginate($perPage),
            ProductoResource::class,
            ApiConstants::class
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/productos',
        summary: 'Crear un nuevo producto',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'precio', 'categoria_id'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Teclado Mecánico'),
                    new OA\Property(property: 'descripcion', type: 'string', nullable: true, example: 'Teclado con switches rojos'),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', minimum: 0, example: 1200.50),
                    new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                    new OA\Property(property: 'activo', type: 'boolean', example: true),
                    new OA\Property(
                        property: 'etiquetas_ids',
                        type: 'array',
                        nullable: true,
                        description: 'Arreglo con los IDs de las etiquetas',
                        items: new OA\Items(type: 'integer', example: 2)
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Producto creado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Producto creado con éxito'),
                        new OA\Property(property: 'producto', ref: '#/components/schemas/Producto'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StoreProductoRequest $request): JsonResponse
    {
        $producto = $this->productoService->create($request->validated());

        return response()->json([
            'mensaje' => 'Producto creado con éxito',
            'producto' => $producto,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/productos/{id}',
        summary: 'Obtener un producto por ID',
        description: 'Público. Devuelve el producto con su categoría y etiquetas.',
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del producto a buscar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles del producto',
                content: new OA\JsonContent(ref: '#/components/schemas/ProductoResource')
            ),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $producto = $this->productoService->getById($id);

        if (! $producto) {
            return ErrorCode::NOT_FOUND->response(message: 'Producto no encontrado');
        }

        return response()->json(new ProductoResource($producto), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/productos/{id}',
        summary: 'Actualizar un producto',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del producto a actualizar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Teclado Mecánico RGB'),
                    new OA\Property(property: 'descripcion', type: 'string', nullable: true, example: 'Teclado con switches azules'),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', minimum: 0, example: 1500.00),
                    new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                    new OA\Property(property: 'activo', type: 'boolean', example: true),
                    new OA\Property(
                        property: 'etiquetas_ids',
                        type: 'array',
                        nullable: true,
                        description: 'Arreglo con los IDs de las etiquetas',
                        items: new OA\Items(type: 'integer', example: 2)
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Producto actualizado con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProductoResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    #[OA\Patch(
        path: '/api/productos/{id}',
        summary: 'Actualizar parcialmente un producto',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del producto a actualizar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Teclado Mecánico RGB'),
                    new OA\Property(property: 'descripcion', type: 'string', nullable: true, example: 'Teclado con switches azules'),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', minimum: 0, example: 1500.00),
                    new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                    new OA\Property(property: 'activo', type: 'boolean', example: true),
                    new OA\Property(
                        property: 'etiquetas_ids',
                        type: 'array',
                        nullable: true,
                        description: 'Arreglo con los IDs de las etiquetas',
                        items: new OA\Items(type: 'integer', example: 2)
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Producto actualizado con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProductoResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(StoreProductoRequest $request, int $id): JsonResponse
    {
        $producto = $this->productoService->update($id, $request->validated());

        if (! $producto) {
            return ErrorCode::NOT_FOUND->response(message: 'Producto no encontrado');
        }

        return response()->json([
            'mensaje' => 'Producto actualizado con éxito',
            'data' => new ProductoResource($producto),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/productos/{id}',
        summary: 'Eliminar un producto',
        tags: ['Productos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del producto a eliminar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto eliminado correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Producto eliminado correctamente'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->productoService->delete($id);

        if (! $deleted) {
            return ErrorCode::NOT_FOUND->response(message: 'Producto no encontrado');
        }

        return response()->json([
            'mensaje' => 'Producto eliminado correctamente',
        ], Response::HTTP_OK);
    }
}
