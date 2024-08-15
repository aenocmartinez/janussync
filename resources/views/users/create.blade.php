@extends('layouts.main')

@section('title', 'Registro de Usuario | JanusSync')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-4xl">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-3xl font-semibold text-center text-blue-800 mb-6">{{ __('Registro de Usuario') }}</h2>
            
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <!-- Agrupación de campos en dos columnas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Campo Nombre -->
                    <div class="mb-3">
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre') }}</label>
                        <input id="name" type="text" class="mt-1 block w-full px-3 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="name" value="{{ old('name') }}" required autocomplete="off" autofocus>
                        @error('name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Campo Email -->
                    <div class="mb-3">
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Correo Electrónico') }}</label>
                        <div class="flex">
                            <input id="email" type="text" class="mt-1 block w-1/2 px-3 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-l-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="email" value="{{ old('email') }}" placeholder="nombre.apellido" required autocomplete="off">
                            <span class="mt-1 inline-flex items-center px-3 py-2 w-1/2 border border-gray-300 bg-gray-50 text-gray-600 text-sm rounded-r-lg">@unicolmayor.edu.co</span>
                        </div>
                        @error('email')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Por favor, ingrese su correo institucional.</p>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="mb-3">
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Contraseña') }}</label>
                        <input id="password" type="password" class="mt-1 block w-full px-3 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="password" value="{{ old('password') }}" required autocomplete="off">
                        @error('password')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror

                        <!-- Feedback visual de validación -->
                        <ul class="text-sm mt-2 space-y-1" id="password-requirements">
                            <li id="length-check" class="text-gray-500">Debe tener al menos 8 caracteres.</li>
                            <li id="uppercase-check" class="text-gray-500">Debe contener al menos una letra mayúscula.</li>
                            <li id="lowercase-check" class="text-gray-500">Debe contener al menos una letra minúscula.</li>
                            <li id="number-check" class="text-gray-500">Debe contener al menos un número.</li>
                            <li id="special-check" class="text-gray-500">Debe contener al menos un carácter especial (. + - _ ! ¡ ( ) ? ¿ $ #).</li>
                        </ul>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-3">
                        <label for="password-confirm" class="block text-sm font-medium text-gray-700">{{ __('Confirmar Contraseña') }}</label>
                        <input id="password-confirm" type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="password_confirmation" required autocomplete="off">
                    </div>

                    <!-- Campo Rol con Select2 -->
                    <div class="mb-3">
                        <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Rol') }}</label>
                        <select id="role" name="role" class="select2 mt-1 block w-full px-3 py-2 border @error('role') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">{{ __('Seleccione un Rol') }}</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Botones de Crear y Cancelar -->
                <div class="mt-4 flex justify-end space-x-4">
                    <button type="button" onclick="window.location.href='{{ route('users.index') }}'" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        {{ __('Crear usuario') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Incluir jQuery desde un CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Incluir Select2 desde un CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#role').select2({
            placeholder: 'Seleccione un Rol',
            allowClear: true
        });

        $('#password').on('input', function() {
            const password = $(this).val();
            const lengthCheck = password.length >= 8;
            const uppercaseCheck = /[A-Z]/.test(password);
            const lowercaseCheck = /[a-z]/.test(password);
            const numberCheck = /[0-9]/.test(password);
            const specialCheck = /[.+\-_\!\¡\(\)\?\¿\$#]/.test(password);

            $('#length-check').toggleClass('text-green-600', lengthCheck).toggleClass('text-gray-500', !lengthCheck);
            $('#uppercase-check').toggleClass('text-green-600', uppercaseCheck).toggleClass('text-gray-500', !uppercaseCheck);
            $('#lowercase-check').toggleClass('text-green-600', lowercaseCheck).toggleClass('text-gray-500', !lowercaseCheck);
            $('#number-check').toggleClass('text-green-600', numberCheck).toggleClass('text-gray-500', !numberCheck);
            $('#special-check').toggleClass('text-green-600', specialCheck).toggleClass('text-gray-500', !specialCheck);
        });
    });
</script>
@endpush
