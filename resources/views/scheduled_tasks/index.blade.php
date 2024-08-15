@extends('layouts.main')

@section('title', 'Gestión de Tareas Programadas | JanusSync')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="flex items-center mb-4 sm:mb-0">
            <i class="fas fa-tasks text-blue-800 text-2xl mr-2"></i>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-800">Gestión de Tareas Programadas</h1>
        </div>
        <a href="{{ route('scheduled-tasks.create') }}" class="w-full sm:w-auto flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 transform hover:-translate-y-1">
            Nueva Tarea
        </a>
    </div>

    <!-- Buscador de Tareas -->
    <form method="GET" action="{{ route('scheduled-tasks.index') }}">
        <div class="relative mb-8 mt-6 max-w-md mx-auto sm:max-w-xl lg:max-w-3xl">
            <input type="text" id="taskSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o frecuencia..." class="w-full px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10 transition ease-in-out duration-150">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
    </form>

    <div class="bg-white shadow-lg rounded-lg overflow-x-auto mt-6">
        <div class="block sm:hidden">
            @forelse($tasks as $task)
                <div class="p-4 border-b border-gray-200">
                    <div class="font-bold text-gray-700">{{ $task->task_name }}</div>
                    <div class="text-gray-500">{{ $task->frequency }}</div>
                    <div class="text-gray-500">{{ $task->execution_info }}</div>
                    <div class="mt-2">
                        <a href="{{ route('scheduled-tasks.edit', $task->id) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                        <button class="text-red-600 hover:text-red-800" onclick="openConfirmDeleteModal('deleteTaskModal{{ $loop->index + 1 }}')">
                            Eliminar
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">No se encontraron tareas programadas.</div>
            @endforelse
        </div>

        <table class="hidden sm:table min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frecuencia</th>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próxima Ejecución</th>
                    <th scope="col" class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="taskTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($tasks as $index => $task)
                    <tr class="hover:bg-gray-100 transition-colors duration-150">
                        <td class="py-4 px-6 text-sm text-gray-700">{{ $task->task_name }}</td>
                        <td class="py-4 px-6 text-sm text-gray-700">{{ $task->frequency }}</td>
                        <td class="py-4 px-6 text-sm text-gray-700">{{ $task->execution_info }}</td>
                        <td class="py-4 px-6 text-sm text-right">
                            <div class="inline-flex items-center space-x-3">
                                <a href="{{ route('scheduled-tasks.edit', $task->id) }}" class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteTaskModal{{ $index + 1 }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 px-6 text-center text-sm text-gray-500">
                            No se encontraron tareas programadas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($tasks->hasPages())
        <x-paginator 
            :currentPage="$tasks->currentPage()" 
            :totalPages="$tasks->lastPage()" 
            :recordsPerPage="$tasks->perPage()" 
            :totalRecords="$tasks->total()" 
            :startRecord="$tasks->firstItem()" 
            :endRecord="$tasks->lastItem()" 
        />
    @endif
</div>

<!-- Modales para Confirmar Eliminación -->
@foreach($tasks as $index => $task)
    <x-modal-delete modalId="deleteTaskModal{{ $index + 1 }}" formId="deleteTaskForm{{ $index + 1 }}" />
@endforeach

<!-- Formularios de eliminación -->
@foreach($tasks as $index => $task)
    <form id="deleteTaskForm{{ $index + 1 }}" action="{{ route('scheduled-tasks.destroy', $task->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script src="{{ asset('js/confirm-delete.js') }}"></script>

<script>
// Función para activar la búsqueda solo al presionar Enter usando jQuery
$('#taskSearch').on('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Evita el comportamiento por defecto
        $(this).closest('form').submit(); // Envía el formulario usando jQuery
    }
});
</script>
@endsection
