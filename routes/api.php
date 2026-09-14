<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EtiquetaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Público: cualquiera puede ver los productos con su categoría y etiquetas
Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth-login');

    // Recuperación de contraseña (públicas)
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth-forgot-password');
    Route::post('verify-reset-token', [AuthController::class, 'verifyResetToken'])->middleware('throttle:auth-verify-reset-token');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth-reset-password');

    // Renovación de token JWT (sin middleware auth:api: admite tokens expirados dentro del refresh_ttl)
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('throttle:auth-refresh');

    // Rutas protegidas (token JWT)
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    // Cliente: crea pedidos y solo ve los suyos. Empleado y admin ven todos.
    Route::middleware('role:cliente|empleado|admin')->group(function () {
        Route::get('pedidos', [PedidoController::class, 'index']);
        Route::post('pedidos', [PedidoController::class, 'store']);
        Route::get('pedidos/{id}', [PedidoController::class, 'show']);
    });

    // Empleado y admin: gestionan categorías, etiquetas y productos (sin eliminar)
    Route::middleware('role:empleado|admin')->group(function () {
        Route::get('categorias', [CategoriaController::class, 'index']);
        Route::post('categorias', [CategoriaController::class, 'store']);
        Route::get('categorias/{id}', [CategoriaController::class, 'show']);
        Route::put('categorias/{id}', [CategoriaController::class, 'update']);
        Route::patch('categorias/{id}', [CategoriaController::class, 'update']);

        Route::get('etiquetas', [EtiquetaController::class, 'index']);
        Route::post('etiquetas', [EtiquetaController::class, 'store']);
        Route::get('etiquetas/{id}', [EtiquetaController::class, 'show']);
        Route::put('etiquetas/{id}', [EtiquetaController::class, 'update']);
        Route::patch('etiquetas/{id}', [EtiquetaController::class, 'update']);

        Route::post('productos', [ProductoController::class, 'store']);
        Route::put('productos/{id}', [ProductoController::class, 'update']);
        Route::patch('productos/{id}', [ProductoController::class, 'update']);
    });

    // Admin: permisos totales (eliminar, usuarios, cambiar estado de pedidos)
    Route::middleware('role:admin')->group(function () {
        Route::delete('productos/{id}', [ProductoController::class, 'destroy']);
        Route::delete('categorias/{id}', [CategoriaController::class, 'destroy']);
        Route::delete('etiquetas/{id}', [EtiquetaController::class, 'destroy']);

        Route::put('pedidos/{id}/estado', [PedidoController::class, 'updateEstado']);

        Route::apiResource('usuarios', UserController::class);
    });
});

// Dashboards por rol (protegidos con token)
Route::middleware('auth:api')->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/dashboard', [DashboardController::class, 'adminDashboard']);
    });

    Route::middleware('role:empleado')->group(function () {
        Route::get('empleado/pedidos', [DashboardController::class, 'empleadoPedidos']);
    });

    Route::middleware('role:cliente')->group(function () {
        Route::get('cliente/perfil', [DashboardController::class, 'clientePerfil']);
    });
});
