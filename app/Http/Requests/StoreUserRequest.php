<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepara los datos para la validación.
     */
    protected function prepareForValidation()
    {
        // Concatenar el dominio al email antes de la validación
        if ($this->filled('email')) {
            $this->merge([
                'email' => $this->email . '@unicolmayor.edu.co',
            ]);
        }
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email', // Verifica que el correo sea único en la tabla users
                'regex:/^[\w\.\-]+@unicolmayor\.edu\.co$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8', // Mínimo 8 caracteres
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.\+\-_!¡()?¿$#]).+$/', // Letras mayúsculas, minúsculas, números y caracteres especiales
                'confirmed'
            ],
            'role' => 'required|exists:roles,id',
        ];
    }

    /**
     * Obtiene los mensajes personalizados para los errores de validación.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'email.regex' => 'El correo electrónico debe ser del dominio @unicolmayor.edu.co.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial (. + - _ ! ¡ ( ) ? ¿ $ #).',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            'role.required' => 'El rol es obligatorio.',
            'role.exists' => 'El rol seleccionado no es válido.',
        ];
    }
}
