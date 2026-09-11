<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\CategoriaServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Support\ApiConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class CategoriaController extends Controller
{
    public function __construct(
        private readonly CategoriaServiceInterface $categoriaService
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/categorias',
        summary: 'Listar categorías',
        description: 'Devuelve las categorías paginadas y ordenadas alfabéticamente.',
        tags: ['Categorías'],
        security: [['bearerAuth' => []]],
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
                description: 'Listado paginado de categorías',
                content: new OA\JsonContent(ref: '#/components/schemas/Paginado')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        return json_paginado(
            $this->categoriaService->paginate($perPage),
            CategoriaResource::class,
            ApiConstants::class
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/categorias',
        summary: 'Crear una nueva categoría',
        tags: ['Categorías'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Tecnología'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Categoría creada con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Categoría creada con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CategoriaResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(CategoriaRequest $request): JsonResponse
    {
        $categoria = $this->categoriaService->create($request->validated());

        return response()->json([
            'mensaje' => 'Categoría creada con éxito',
            'data' => new CategoriaResource($categoria),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/categorias/{id}',
        summary: 'Mostrar una categoría por ID',
        tags: ['Categorías'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la categoría a buscar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles de la categoría',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoriaResource')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Categoría no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $categoria = $this->categoriaService->getById($id);

        if (! $categoria) {
            return ErrorCode::NOT_FOUND->response(message: 'Categoría no encontrada');
        }

        return response()->json(new CategoriaResource($categoria), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/categorias/{id}',
        summary: 'Actualizar una categoría',
        tags: ['Categorías'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la categoría a actualizar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Tecnología'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoría actualizada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Categoría actualizada'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CategoriaResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Categoría no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(CategoriaRequest $request, int $id): JsonResponse
    {
        $categoria = $this->categoriaService->update($id, $request->validated());

        if (! $categoria) {
            return ErrorCode::NOT_FOUND->response(message: 'Categoría no encontrada');
        }

        return response()->json([
            'mensaje' => 'Categoría actualizada',
            'data' => new CategoriaResource($categoria),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/categorias/{id}',
        summary: 'Eliminar una categoría',
        tags: ['Categorías'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la categoría a eliminar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoría eliminada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Categoría eliminada'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Categoría no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->categoriaService->delete($id);

        if (! $deleted) {
            return ErrorCode::NOT_FOUND->response(message: 'Categoría no encontrada');
        }

        return response()->json([
            'mensaje' => 'Categoría eliminada',
        ], Response::HTTP_OK);
    }
}
