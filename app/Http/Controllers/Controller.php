<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'API de Productos',
    description: 'API REST para gestionar productos, categorías, etiquetas, pedidos y usuarios. '.
                  'Utiliza autenticación JWT: primero inicia sesión en POST /api/auth/login y '.
                  'luego envía el token en el header `Authorization: Bearer <token>`.'.
                  ' El catálogo de productos es público; los pedidos y la gestión por rol exigen token.'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Servidor local'
)]
#[OA\Tag(name: 'Autenticación', description: 'Registro, inicio y cierre de sesión (JWT)')]
#[OA\Tag(name: 'Productos', description: 'Catálogo público de productos (ver) y gestión (empleado/admin)')]
#[OA\Tag(name: 'Pedidos', description: 'Compras de clientes y gestión de estado (solo admin)')]
#[OA\Tag(name: 'Categorías', description: 'Gestión de categorías (empleado/admin)')]
#[OA\Tag(name: 'Etiquetas', description: 'Gestión de etiquetas (empleado/admin)')]
#[OA\Tag(name: 'Usuarios', description: 'Gestión de usuarios (solo admin)')]
#[OA\Tag(name: 'Roles', description: 'Endpoints protegidos por rol (admin, empleado, cliente)')]
abstract class Controller
{
    //
}
