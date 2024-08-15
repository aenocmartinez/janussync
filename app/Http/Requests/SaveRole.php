<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveRole extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'unique:roles,name',
                'regex:/^[\p{L}][\p{L}\p{N}\s-]*$/u', // Permite letras con acentos, números, espacios, y guiones; no puede comenzar con un número
            ],
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }

    /**
     * Sanitize input before validation.
     *
     * @return array
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->input('name')),
        ]);
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.min' => 'El nombre del rol debe tener al menos 5 caracteres.',
            'name.max' => 'El nombre del rol no debe exceder los 100 caracteres.',
            'name.unique' => 'El nombre del rol ya está en uso. Por favor, elige otro.',
            'name.regex' => 'El nombre del rol debe comenzar con una letra y puede incluir letras, números, espacios y guiones, pero no puede iniciar con un número.',
            'permissions.array' => 'Los permisos deben ser un arreglo.',
            'permissions.*.integer' => 'Cada permiso debe ser un número válido.',
            'permissions.*.exists' => 'El permiso seleccionado no es válido.',
        ];
    }
}
