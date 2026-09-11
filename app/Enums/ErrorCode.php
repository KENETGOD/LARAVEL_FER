<?php

namespace App\Enums;

use Illuminate\Http\JsonResponse;

enum ErrorCode: string
{
    case USER_NOT_FOUND = 'USER_NOT_FOUND';
    case NOT_FOUND = 'NOT_FOUND';
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case FORBIDDEN = 'FORBIDDEN';
    case INVALID_TOKEN = 'INVALID_TOKEN';
    case INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';

    public function message(): string
    {
        return match ($this) {
            self::USER_NOT_FOUND => 'Usuario no encontrado',
            self::NOT_FOUND => 'Recurso no encontrado',
            self::VALIDATION_ERROR => 'Los datos enviados no son válidos',
            self::METHOD_NOT_ALLOWED => 'Método no permitido para esta ruta',
            self::UNAUTHORIZED => 'No autenticado',
            self::FORBIDDEN => 'No tienes permisos para realizar esta acción',
            self::INVALID_TOKEN => 'El enlace de restablecimiento no es válido o ha expirado',
            self::INTERNAL_SERVER_ERROR => 'Ocurrió un error interno en el servidor',
        };
    }

    public function statusCode(): int
    {
        return match ($this) {
            self::USER_NOT_FOUND, self::NOT_FOUND => 404,
            self::VALIDATION_ERROR => 422,
            self::METHOD_NOT_ALLOWED => 405,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::INVALID_TOKEN => 400,
            self::INTERNAL_SERVER_ERROR => 500,
        };
    }

    public function response(?string $message = null, array $details = []): JsonResponse
    {
        $data = [
            'error' => $this->value,
            'message' => $message ?? $this->message(),
        ];

        if ($details !== []) {
            $data['details'] = $details;
        }

        return response()->json($data, $this->statusCode());
    }
}
