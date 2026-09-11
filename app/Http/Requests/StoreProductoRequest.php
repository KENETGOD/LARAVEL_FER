<?php

namespace App\Http\Requests;

use App\Enums\ErrorCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // función para determinar si es una actualización o creación
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'categoria_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:categorias,id'], // Validación para el campo 'categoria_id'
            'nombre' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'activo' => ['nullable', 'boolean'],
            'etiquetas_ids' => ['nullable', 'array'], // Validación para el campo 'etiquetas_ids'
            'etiquetas_ids.*' => ['integer', 'exists:etiquetas,id'], // Validación para cada ID de etiqueta en el arreglo
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ErrorCode::VALIDATION_ERROR->response(details: $validator->errors()->toArray())
        );
    }
}
