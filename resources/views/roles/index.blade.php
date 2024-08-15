@extends('layouts.main')

@section('title', 'Gestión de Usuarios | JanusSync')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="flex items-center mb-4 sm:mb-0">
            <i class="fas fa-user-tag text-blue-800 text-2xl mr-2"></i>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-800">Gestión de Roles</h1>
        </div>
        <button class="w-full sm:w-auto flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 transform hover:-translate-y-1" onclick="window.location.href='{{ route('roles.create') }}'">
            Nuevo Rol
        </button>
    </div>

    <!-- Buscador de Roles -->
    <form method="GET" action="{{ route('roles.index') }}">
        <div class="relative mb-8 mt-6 max-w-md mx-auto sm:max-w-xl lg:max-w-3xl">
            <input type="text" id="userSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o rol..." class="w-full px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10 transition ease-in-out duration-150">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
    </form>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                @if($roles->isEmpty())
                    <tr>
                        <td colspan="3" class="py-4 px-6 text-center text-sm text-gray-500">
                            No se encontraron roles.
                        </td>
                    </tr>
                @else
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-100 transition-colors duration-150">
                            <td class="py-4 px-6 text-sm text-gray-700">{{ $role->name }}</td>
                            <td class="py-4 px-6 text-sm text-right">
                                <div class="inline-flex items-center space-x-3">
                                    <a href="{{ route('roles.edit', $role->id) }}" class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal{{ $loop->index + 1 }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Componente de Paginador -->
    @if($roles->hasPages())
        <x-paginator 
            :currentPage="$roles->currentPage()" 
            :totalPages="$roles->lastPage()" 
            :recordsPerPage="$roles->perPage()" 
            :totalRecords="$roles->total()" 
            :startRecord="$roles->firstItem()" 
            :endRecord="$roles->lastItem()" 
        />
    @endif
</div>

<!-- Componentes Modal para Confirmar Eliminación para cada usuario -->
@foreach($roles as $role)
    <x-modal-delete modalId="deleteUserModal{{ $loop->index + 1 }}" formId="deleteUserForm{{ $loop->index + 1 }}" />
@endforeach

<!-- Formularios de eliminación para cada usuario -->
@foreach($roles as $role)
    <form id="deleteUserForm{{ $loop->index + 1 }}" action="{{ route('roles.destroy', ['role' => $role->id]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script src="{{ asset('js/confirm-delete.js') }}"></script>
@endsection
