<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/admin/dashboard',
        summary: 'Panel de administrador',
        description: 'Solo accesible con token JWT de un usuario con rol admin.',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Acceso concedido'),
            new OA\Response(response: 401, description: 'Token inválido o ausente'),
            new OA\Response(response: 403, description: 'Rol sin permisos para acceder'),
        ]
    )]
    public function adminDashboard(): JsonResponse
    {
        return response()->json(['mensaje' => '¡Bienvenido al panel de Administrador! Todo funciona perfecto.']);
    }

    #[OA\Get(
        path: '/api/empleado/pedidos',
        summary: 'Gestión de pedidos para empleados',
        description: 'Solo accesible con token JWT de un usuario con rol empleado.',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Acceso concedido'),
            new OA\Response(response: 401, description: 'Token inválido o ausente'),
            new OA\Response(response: 403, description: 'Rol sin permisos para acceder'),
        ]
    )]
    public function empleadoPedidos(): JsonResponse
    {
        return response()->json(['mensaje' => 'Área de gestión de pedidos para Empleados']);
    }

    #[OA\Get(
        path: '/api/cliente/perfil',
        summary: 'Perfil del cliente',
        description: 'Solo accesible con token JWT de un usuario con rol cliente.',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Acceso concedido'),
            new OA\Response(response: 401, description: 'Token inválido o ausente'),
            new OA\Response(response: 403, description: 'Rol sin permisos para acceder'),
        ]
    )]
    public function clientePerfil(): JsonResponse
    {
        return response()->json(['mensaje' => 'Bienvenido a tu perfil de Cliente']);
    }
}
