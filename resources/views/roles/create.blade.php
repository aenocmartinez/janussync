@extends('layouts.main')

@section('title', 'Crear Rol | JanusSync')

@section('content')

<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-blue-800 mb-6">Crear Nuevo Rol</h1>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <!-- Campo Nombre -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="off" required
                    class="mt-1 block w-full px-3 py-2 border 
                    @error('name') border-red-500 @else border-gray-300 @enderror
                    rounded-md shadow-sm focus:outline-none 
                    @error('name') focus:ring-red-500 focus:border-red-500 @else focus:ring-blue-500 focus:border-blue-500 @enderror 
                    sm:text-sm">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Lista de Permisos con Checkboxes -->
            <div class="mb-6">
                <label for="permissions" class="block text-sm font-medium text-gray-700">Permisos</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($permissions as $permission)
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('permissions')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones de Guardar y Cancelar -->
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="window.location.href='{{ route('roles.index') }}'" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
