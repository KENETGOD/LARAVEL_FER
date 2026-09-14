<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\PedidoServiceInterface;
use App\Enums\ErrorCode;
use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoEstadoRequest;
use App\Http\Resources\PedidoResource;
use App\Support\ApiConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class PedidoController extends Controller
{
    public function __construct(
        private readonly PedidoServiceInterface $pedidoService
    ) {}

    #[OA\Get(
        path: '/api/pedidos',
        summary: 'Listar pedidos',
        description: 'El cliente ve solo sus pedidos; el empleado y el admin ven todos.',
        tags: ['Pedidos'],
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
                description: 'Listado paginado de pedidos',
                content: new OA\JsonContent(ref: '#/components/schemas/Paginado')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: 'Rol sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        return json_paginado(
            $this->pedidoService->paginateFor(auth('api')->user(), $perPage),
            PedidoResource::class,
            ApiConstants::class
        );
    }

    #[OA\Post(
        path: '/api/pedidos',
        summary: 'Crear un pedido',
        description: 'Crea un pedido con los productos indicados. El precio se toma como instantánea al momento de comprar.',
        tags: ['Pedidos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(
                            required: ['producto_id', 'cantidad'],
                            properties: [
                                new OA\Property(property: 'producto_id', type: 'integer', example: 1),
                                new OA\Property(property: 'cantidad', type: 'integer', example: 2),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pedido creado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Pedido creado con éxito'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PedidoResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: 'Rol sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StorePedidoRequest $request): JsonResponse
    {
        $pedido = $this->pedidoService->create(auth('api')->user(), $request->validated());

        return response()->json([
            'mensaje' => 'Pedido creado con éxito',
            'data' => new PedidoResource($pedido),
        ], Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/pedidos/{id}',
        summary: 'Mostrar un pedido',
        description: 'El cliente solo ve pedidos propios; el empleado y el admin ven cualquiera.',
        tags: ['Pedidos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del pedido',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles del pedido',
                content: new OA\JsonContent(ref: '#/components/schemas/PedidoResource')
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: 'No puedes ver un pedido de otro usuario', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Pedido no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $pedido = $this->pedidoService->getForUser(auth('api')->user(), $id);

        if (! $pedido) {
            return ErrorCode::NOT_FOUND->response(message: 'Pedido no encontrado');
        }

        return response()->json(new PedidoResource($pedido), Response::HTTP_OK);
    }

    #[OA\Put(
        path: '/api/pedidos/{id}/estado',
        summary: 'Cambiar el estado de un pedido',
        description: 'Solo el admin puede mover los estados respetando las transiciones válidas.',
        tags: ['Pedidos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del pedido',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['estado'],
                properties: [
                    new OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'], example: 'pagado'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado actualizado',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'mensaje', type: 'string', example: 'Estado actualizado a Pagado'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/PedidoResource'),
                ])
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: 'Solo el admin', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Pedido no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Transición de estado no válida', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function updateEstado(UpdatePedidoEstadoRequest $request, int $id): JsonResponse
    {
        $estado = EstadoPedido::from($request->validated('estado'));

        $pedido = $this->pedidoService->updateEstado($id, $estado);

        if (! $pedido) {
            return ErrorCode::NOT_FOUND->response(message: 'Pedido no encontrado');
        }

        return response()->json([
            'mensaje' => 'Estado actualizado a '.EstadoPedido::from($pedido->estado)->label(),
            'data' => new PedidoResource($pedido),
        ], Response::HTTP_OK);
    }
}
