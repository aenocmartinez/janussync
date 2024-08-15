<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Puedes ajustar esto según tus necesidades de autorización
    }

    /**
     * Preparar los datos para la validación.
     */
    protected function prepareForValidation()
    {
        // Concatenar la parte del nombre del email con el dominio
        $email = $this->input('email') . '@unicolmayor.edu.co';

        // Reemplazar el valor del email con el email completo
        $this->merge([
            'email' => $email,
        ]);
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user'); // Obtiene el ID del usuario desde la ruta

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // Ignora la regla de unicidad si es el mismo email del usuario actual
                'regex:/^[a-zA-Z0-9._%+-]+@unicolmayor\.edu\.co$/i' // Solo correos del dominio especificado
            ],
            'password' => [
                'nullable', // Permite que la contraseña sea opcional
                'string',
                'min:8', // Mínimo 8 caracteres
                'regex:/[A-Z]/', // Al menos una letra mayúscula
                'regex:/[a-z]/', // Al menos una letra minúscula
                'regex:/[0-9]/', // Al menos un número
                'regex:/[.+\-_\!\¡\(\)\?\¿\$#]/' // Al menos un carácter especial
            ],
            'role' => 'required|exists:roles,id', // Verifica que el rol exista en la tabla roles
        ];
    }

    /**
     * Obtener los mensajes de error personalizados para las reglas de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'email.regex' => 'El correo electrónico debe pertenecer al dominio @unicolmayor.edu.co.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            'role.required' => 'El rol es obligatorio.',
            'role.exists' => 'El rol seleccionado no es válido.',
        ];
    }
}
