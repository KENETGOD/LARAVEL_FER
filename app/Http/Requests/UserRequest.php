<?php

namespace App\Http\Requests;

use App\Enums\ErrorCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'email' => [$isUpdate ? 'sometimes' : 'required', 'email', 'unique:users,email,'.$this->route('usuario')],
            'password' => [$isUpdate ? 'sometimes' : 'required', 'string', 'min:8'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ErrorCode::VALIDATION_ERROR->response(details: $validator->errors()->toArray())
        );
    }
}
