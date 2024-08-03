@extends('layouts.main')

@section('title', 'Gestión de Usuarios | JanusSync')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-extrabold tracking-tight mb-4 text-blue-800 text-right border-b-2 sm:border-b-0 border-gray-300 pb-2 flex items-center justify-end">
        <span class="mr-2">Gestión de Usuarios</span>
        <i class="fas fa-users"></i>
    </h1>
    
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="w-1/2 py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="w-1/4 py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th scope="col" class="w-1/4 py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">Juan Carlos Pérez García</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Administrador</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-2">
                            <button class="relative text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                                <x-tooltip>Editar</x-tooltip>
                            </button>
                            <button class="relative text-gray-500 hover:text-red-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal1')">
                                <i class="fas fa-trash"></i>
                                <x-tooltip>Eliminar</x-tooltip>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">María Fernanda García López</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Editor</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-2">
                            <button class="relative text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                                <x-tooltip>Editar</x-tooltip>
                            </button>
                            <button class="relative text-gray-500 hover:text-red-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal2')">
                                <i class="fas fa-trash"></i>
                                <x-tooltip>Eliminar</x-tooltip>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">Carlos Andrés Fernández Ortiz</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Colaborador</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-2">
                            <button class="relative text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                                <x-tooltip>Editar</x-tooltip>
                            </button>
                            <button class="relative text-gray-500 hover:text-red-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal3')">
                                <i class="fas fa-trash"></i>
                                <x-tooltip>Eliminar</x-tooltip>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">Lucía María López Martínez</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Administrador</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-2">
                            <button class="relative text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                                <x-tooltip>Editar</x-tooltip>
                            </button>
                            <button class="relative text-gray-500 hover:text-red-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal4')">
                                <i class="fas fa-trash"></i>
                                <x-tooltip>Eliminar</x-tooltip>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">José Antonio Martínez Sánchez</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Editor</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-2">
                            <button class="relative text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                                <x-tooltip>Editar</x-tooltip>
                            </button>
                            <button class="relative text-gray-500 hover:text-red-600 focus:outline-none transition duration-150 rounded-full p-2 tooltip-button" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal5')">
                                <i class="fas fa-trash"></i>
                                <x-tooltip>Eliminar</x-tooltip>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Componentes Modal para Confirmar Eliminación para cada usuario -->
<x-modal-delete modalId="deleteUserModal1" formId="deleteUserForm1" />
<x-modal-delete modalId="deleteUserModal2" formId="deleteUserForm2" />
<x-modal-delete modalId="deleteUserModal3" formId="deleteUserForm3" />
<x-modal-delete modalId="deleteUserModal4" formId="deleteUserForm4" />
<x-modal-delete modalId="deleteUserModal5" formId="deleteUserForm5" />

<!-- Formularios de eliminación para cada usuario -->
<form id="deleteUserForm1" action="{{ route('users.destroy', ['user' => 1]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="deleteUserForm2" action="{{ route('users.destroy', ['user' => 2]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="deleteUserForm3" action="{{ route('users.destroy', ['user' => 3]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="deleteUserForm4" action="{{ route('users.destroy', ['user' => 4]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="deleteUserForm5" action="{{ route('users.destroy', ['user' => 5]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
function editUser() {
    // Lógica para editar un usuario
}
</script>

@endsection
