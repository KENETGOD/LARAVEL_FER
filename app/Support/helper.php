<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

if (! function_exists('json_paginado')) {
    /**
     * Retorna una respuesta JSON para evitar repetir código en los controladores.
     */
    function json_paginado(LengthAwarePaginator $paginator, string $resourceClass, string $constantsClass): JsonResponse
    {
        // Resolvemos la colección usando el API Resource pasado por parámetro
        $collection = $resourceClass::collection($paginator)->resolve();

        // Estructura
        return response()->json([
            $constantsClass::LLAVE_ESTATUS => $constantsClass::LLAVE_EXITOSO,
            $constantsClass::LLAVE_DATA => [
                'current_page' => $paginator->currentPage(),
                $constantsClass::LLAVE_DATA => $collection,
                'first_page_url' => $paginator->url(1),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'last_page_url' => $paginator->url($paginator->lastPage()),
                'links' => [
                    [
                        'url' => $paginator->previousPageUrl(),
                        'label' => 'pagination.previous',
                        'active' => $paginator->currentPage() > 1,
                    ],
                    [
                        'url' => $paginator->url($paginator->currentPage()),
                        'label' => (string) $paginator->currentPage(),
                        'active' => true,
                    ],
                    [
                        'url' => $paginator->nextPageUrl(),
                        'label' => 'pagination.next',
                        'active' => $paginator->hasMorePages(),
                    ],
                ],
                'next_page_url' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ], Response::HTTP_OK);
    }
}
