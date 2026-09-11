<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\EtiquetaServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\EtiquetaRequest;
use App\Http\Resources\EtiquetaResource;
use App\Support\ApiConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class EtiquetaController extends Controller
{
    public function __construct(
        private readonly EtiquetaServiceInterface $etiquetaService
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/etiquetas',
        summary: 'Listar etiquetas',
        description: 'Devuelve las etiquetas paginadas y ordenadas alfabéticamente.',
        tags: ['Etiquetas'],
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
                description: 'Listado paginado de etiquetas',
                content: new OA\JsonContent(ref: '#/components/schemas/Paginado')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        return json_paginado(
            $this->etiquetaService->paginate($perPage),
            EtiquetaResource::class,
            ApiConstants::class
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/etiquetas',
        summary: 'Crear una nueva etiqueta',
        tags: ['Etiquetas'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Oferta'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Etiqueta creada con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Etiqueta creada con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/EtiquetaResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(EtiquetaRequest $request): JsonResponse
    {
        $etiqueta = $this->etiquetaService->create($request->validated());

        return response()->json([
            'mensaje' => 'Etiqueta creada con éxito',
            'data' => new EtiquetaResource($etiqueta),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/etiquetas/{id}',
        summary: 'Mostrar una etiqueta por ID',
        tags: ['Etiquetas'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la etiqueta a buscar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles de la etiqueta',
                content: new OA\JsonContent(ref: '#/components/schemas/EtiquetaResource')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Etiqueta no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $etiqueta = $this->etiquetaService->getById($id);

        if (! $etiqueta) {
            return ErrorCode::NOT_FOUND->response(message: 'Etiqueta no encontrada');
        }

        return response()->json(new EtiquetaResource($etiqueta), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/etiquetas/{id}',
        summary: 'Actualizar una etiqueta',
        tags: ['Etiquetas'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la etiqueta a actualizar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Oferta'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Etiqueta actualizada con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Etiqueta actualizada con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/EtiquetaResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Etiqueta no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(EtiquetaRequest $request, int $id): JsonResponse
    {
        $etiqueta = $this->etiquetaService->update($id, $request->validated());

        if (! $etiqueta) {
            return ErrorCode::NOT_FOUND->response(message: 'Etiqueta no encontrada');
        }

        return response()->json([
            'mensaje' => 'Etiqueta actualizada con éxito',
            'data' => new EtiquetaResource($etiqueta),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/etiquetas/{id}',
        summary: 'Eliminar una etiqueta',
        tags: ['Etiquetas'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la etiqueta a eliminar',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Etiqueta eliminada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Etiqueta eliminada'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Etiqueta no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->etiquetaService->delete($id);

        if (! $deleted) {
            return ErrorCode::NOT_FOUND->response(message: 'Etiqueta no encontrada');
        }

        return response()->json([
            'mensaje' => 'Etiqueta eliminada',
        ], Response::HTTP_OK);
    }
}
