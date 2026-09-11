<?php

namespace App\Http\Requests;

use App\Enums\ErrorCode;
use App\Enums\EstadoPedido;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePedidoEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'string', Rule::enum(EstadoPedido::class)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ErrorCode::VALIDATION_ERROR->response(details: $validator->errors()->toArray())
        );
    }
}
