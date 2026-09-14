<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\AuthServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyResetTokenRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use OpenApi\Attributes as OA;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Registrar nuevo usuario',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Juan Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Usuario registrado con éxito'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                        new OA\Property(
                            property: 'authorization',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1Qi...'),
                                new OA\Property(property: 'type', type: 'string', example: 'bearer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'mensaje' => 'Usuario registrado con éxito',
            'user' => new UserResource($result['user']),
            'authorization' => [
                'token' => $result['token'],
                'type' => 'bearer',
            ],
        ], Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Iniciar sesión',
        description: 'Devuelve el token JWT que se usa para acceder a los endpoints protegidos.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Inicio de sesión exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Inicio de sesión exitoso'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                        new OA\Property(
                            property: 'authorization',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1Qi...'),
                                new OA\Property(property: 'type', type: 'string', example: 'bearer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales incorrectas', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());

        if (! $token) {
            return ErrorCode::UNAUTHORIZED->response(message: 'Credenciales incorrectas');
        }

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'user' => new UserResource(auth('api')->user()),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Cerrar sesión',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Sesión cerrada exitosamente'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o ausente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'mensaje' => 'Sesión cerrada exitosamente',
        ]);
    }

    #[OA\Post(
        path: '/api/auth/forgot-password',
        summary: 'Solicitar enlace de restablecimiento',
        description: 'Envía por correo un enlace para restablecer la contraseña. Siempre responde el mismo mensaje para no revelar si el correo existe.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Solicitud procesada (el correo se envía solo si existe)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated());

        return response()->json([
            'mensaje' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.',
        ]);
    }

    #[OA\Post(
        path: '/api/auth/reset-password',
        summary: 'Restablecer contraseña',
        description: 'Valida el token recibido por correo (de un solo uso, expira a los 60 minutos) y actualiza la contraseña.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'token', type: 'string', example: 'abc123'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'nueva123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'nueva123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contraseña restablecida con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Contraseña restablecida con éxito.'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Token inválido o expirado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'mensaje' => 'Contraseña restablecida con éxito.',
            ]);
        }

        if ($status === Password::INVALID_USER) {
            return ErrorCode::USER_NOT_FOUND->response();
        }

        return ErrorCode::INVALID_TOKEN->response();
    }

    #[OA\Post(
        path: '/api/auth/verify-reset-token',
        summary: 'Validar token de restablecimiento',
        description: 'Comprueba si el token sigue siendo válido sin cambiar la contraseña. Útil para que el frontend valide el enlace antes de mostrar el formulario.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'token', type: 'string', example: 'abc123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'El token es válido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'El enlace de restablecimiento es válido.'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Token inválido o expirado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function verifyResetToken(VerifyResetTokenRequest $request): JsonResponse
    {
        $status = $this->authService->verifyResetToken($request->email, $request->token);

        if ($status === null) {
            return ErrorCode::USER_NOT_FOUND->response();
        }

        if ($status === false) {
            return ErrorCode::INVALID_TOKEN->response();
        }

        return response()->json([
            'mensaje' => 'El enlace de restablecimiento es válido.',
        ]);
    }

    #[OA\Post(
        path: '/api/auth/refresh',
        summary: 'Renovar token JWT',
        description: 'Genera un nuevo token a partir del actual (puede estar expirado mientras esté dentro del refresh_ttl, 14 días por defecto). El token anterior queda invalidado (blacklist).',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token renovado con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Token renovado con éxito'),
                        new OA\Property(
                            property: 'authorization',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1Qi...'),
                                new OA\Property(property: 'type', type: 'string', example: 'bearer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o fuera de la ventana de refresh', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $token = $this->authService->refresh();
        } catch (JWTException) {
            return ErrorCode::UNAUTHORIZED->response();
        }

        return response()->json([
            'mensaje' => 'Token renovado con éxito',
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }
}
