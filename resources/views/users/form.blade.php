@php
    // Extraer la parte local y el dominio del correo electrónico
    $emailParts = explode('@', old('email', $user->email ?? ''));
    $emailLocal = $emailParts[0] ?? '';
    $emailDomain = $emailParts[1] ?? 'unicolmayor.edu.co';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Campo Nombre -->
    <div class="mb-3">
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre') }}</label>
        <input id="name" type="text" class="mt-1 block w-full px-3 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="name" value="{{ old('name', $user->name ?? '') }}" required autocomplete="off" autofocus>
        @error('name')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Campo Email -->
    <div class="mb-3">
        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Correo Electrónico') }}</label>
        <div class="flex">
            <input id="email" type="text" class="mt-1 block w-1/2 px-3 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-l-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="email" value="{{ $emailLocal }}" placeholder="nombre.apellido" required autocomplete="off">
            <span class="mt-1 inline-flex items-center px-3 py-2 w-1/2 border border-gray-300 bg-gray-50 text-gray-600 text-sm rounded-r-lg">{{ $emailDomain }}</span>
        </div>
        @error('email')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
        <p class="text-gray-500 text-sm mt-1">Por favor, ingrese su correo institucional.</p>
    </div>

    <!-- Campo Contraseña -->
    <div class="mb-3">
        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Contraseña') }}</label>
        <input id="password" type="password" class="mt-1 block w-full px-3 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="password" autocomplete="off" {{ !isset($user) ? 'required' : '' }}>
        @error('password')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
        <!-- Información sobre los requisitos de la contraseña -->
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
        <input id="password-confirm" type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="password_confirmation" autocomplete="off" {{ !isset($user) ? 'required' : '' }}>
    </div>

    <!-- Campo Rol con Select2 -->
    <div class="mb-3">
        <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Rol') }}</label>
        <select id="role" name="role" class="select2 mt-1 block w-full px-3 py-2 border @error('role') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            <option value="">{{ __('Seleccione un Rol') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ old('role', $user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Botones de Crear/Actualizar y Cancelar -->
<div class="mt-4 flex justify-end space-x-4">
    <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
        {{ __('Cancelar') }}
    </a>
    <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
        {{ isset($user) && $user->id > 0 ? __('Actualizar Usuario') : __('Crear Usuario') }}
    </button>
</div>
