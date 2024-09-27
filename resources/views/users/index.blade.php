@extends('layouts.main')

@section('title', 'Gestión de Usuarios | JanusSync')

@section('content')

<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="flex items-center mb-4 sm:mb-0">
            <i class="fas fa-users text-blue-800 text-2xl mr-2"></i>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-800">Gestión de Usuarios</h1>
        </div>
        @can('Crear usuario')            
            <a href="{{ route('users.create') }}" class="w-full sm:w-auto flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 transform hover:-translate-y-1">
                Nuevo Usuario
            </a>
        @endcan
    </div>

    <!-- Buscador de Usuarios -->
    <form method="GET" action="{{ route('users.index') }}">
        <div class="relative mb-8 mt-6 max-w-md mx-auto sm:max-w-xl lg:max-w-3xl">
            <input type="text" id="userSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, email o rol..." class="w-full px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10 transition ease-in-out duration-150">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
    </form>

    <div class="bg-white shadow-lg rounded-lg overflow-x-auto mt-6">
        <div class="block sm:hidden">
            @forelse($users as $user)
                <div class="p-4 border-b border-gray-200">
                    <div class="font-bold text-gray-700">{{ $user['name'] }}</div>
                    <div class="text-gray-500">{{ $user['email'] }}</div>
                    <div class="text-gray-500">{{ $user->getNameRole() }}</div>
                    <div class="mt-2">
                        @if (auth()->user()->can('Actualizar usuario'))
                            <a href="{{ route('users.edit', $user['id']) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                        @endif

                        @if (auth()->user()->can('Eliminar usuario'))
                        <button class="text-red-600 hover:text-red-800" onclick="openConfirmDeleteModal('deleteUserModal{{ $loop->index + 1 }}')">
                            Eliminar
                        </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">No se encontraron usuarios.</div>
            @endforelse
        </div>

        <table class="hidden sm:table min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th scope="col" class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($users as $index => $user)
                    <tr class="hover:bg-gray-100 transition-colors duration-150">
                        <td class="py-4 px-6 text-sm text-gray-700">{{ $user['name'] }}</td>
                        <td class="py-4 px-6 text-sm text-gray-700 truncate" style="max-width: 200px;">{{ $user['email'] }}</td>
                        <td class="py-4 px-6 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->getNameRole() == 'Administrador' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">{{ $user->getNameRole() }}</span>
                        </td>
                        <td class="py-4 px-6 text-sm text-right">
                            <div class="inline-flex items-center space-x-3">
                                <a href="{{ route('users.edit', $user['id']) }}" class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal{{ $index + 1 }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 px-6 text-center text-sm text-gray-500">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Componente de Paginador -->
    @if($users->hasPages())
        <x-paginator 
            :currentPage="$users->currentPage()" 
            :totalPages="$users->lastPage()" 
            :recordsPerPage="$users->perPage()" 
            :totalRecords="$users->total()" 
            :startRecord="$users->firstItem()" 
            :endRecord="$users->lastItem()" 
        />
    @endif
</div>

<!-- Componentes Modal para Confirmar Eliminación para cada usuario -->
@foreach($users as $index => $user)
    <x-modal-delete modalId="deleteUserModal{{ $index + 1 }}" formId="deleteUserForm{{ $index + 1 }}" />
@endforeach

<!-- Formularios de eliminación para cada usuario -->
@foreach($users as $index => $user)
    <form id="deleteUserForm{{ $index + 1 }}" action="{{ route('users.destroy', ['user' => $user['id']]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script src="{{ asset('js/confirm-delete.js') }}"></script>

<script>
// Paginación de ejemplo
let currentPage = 1;
const totalPages = 10; 
const recordsPerPage = 10;
const totalRecords = 50; 

function updatePagination() {
    const startRecord = (currentPage - 1) * recordsPerPage + 1;
    const endRecord = Math.min(currentPage * recordsPerPage, totalRecords);

    document.getElementById('startRecord').textContent = startRecord;
    document.getElementById('endRecord').textContent = endRecord;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
    }
}

function gotoPage(page) {
    currentPage = parseInt(page);
    updatePagination();
}

document.addEventListener('DOMContentLoaded', function () {
    updatePagination();
});

// Función para filtrar la tabla de usuarios
document.getElementById('userSearch').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let rows = document.getElementById('userTableBody').getElementsByTagName('tr');
    let hasResults = false;

    for (let i = 0; i < rows.length; i++) {
        let name = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        let email = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        let role = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
        if (name.indexOf(filter) > -1 || email.indexOf(filter) > -1 || role.indexOf(filter) > -1) {
            rows[i].style.display = '';
            hasResults = true;
        } else {
            rows[i].style.display = 'none';
        }
    }

    // Muestra el mensaje cuando no hay resultados
    document.getElementById('noResultsMessage').style.display = hasResults ? 'none' : '';
});
</script>

@endsection
