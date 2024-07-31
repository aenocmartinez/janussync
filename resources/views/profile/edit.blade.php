@extends('layouts.main')

@section('title', 'Editar Perfil')

@section('content')
<div class="container mx-auto p-4 bg-white shadow-md rounded-lg max-w-lg">
    <h1 class="text-2xl font-bold mb-2 text-blue-800 text-right">Editar Perfil</h1>
    
    @component('components.breadcrumb')
        @slot('items', [
            ['name' => 'Dashboard', 'url' => url('/dashboard')],
            ['name' => 'Editar Perfil']
        ])
    @endcomponent

    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-1">Nombre:</label>
            <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-1">Correo Electrónico:</label>
            <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-semibold mb-1">Nueva Contraseña:</label>
            <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dejar en blanco si no desea cambiar">
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 font-semibold mb-1">Confirmar Contraseña:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
