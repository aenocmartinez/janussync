@extends('layouts.main')

@section('title', 'Crear Rol | JanusSync')

@section('content')

<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-blue-800 mb-6">Crear Nuevo Rol</h1>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            @include('roles.form', ['permissions' => $permissions])

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