@extends('layouts.main')

@section('title', 'Gestión de Usuarios | JanusSync')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="flex items-center mb-4 sm:mb-0">
            <i class="fas fa-users text-blue-800 text-2xl mr-2"></i>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-800">Gestión de Usuarios</h1>
        </div>
        <button class="w-full sm:w-auto flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 transform hover:-translate-y-1" onclick="window.location.href='{{ route('users.create') }}'">
            Nuevo Usuario
        </button>
    </div>

    <!-- Buscador de Usuarios -->
    <div class="relative mb-8 mt-6 max-w-md mx-auto sm:max-w-xl lg:max-w-3xl">
        <input type="text" id="userSearch" placeholder="Buscar por nombre o rol..." class="w-full px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10 transition ease-in-out duration-150">
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th scope="col" class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">Juan Carlos Pérez García</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Administrador</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-3">
                            <button class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal1')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">María Fernanda García López</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Monitoreo</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-3">
                            <button class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal2')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">Carlos Andrés Fernández Ortiz</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Monitoreo</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-3">
                            <button class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal3')">
                                <i class="fas fa-trash"></i>
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
                        <div class="inline-flex items-center space-x-3">
                            <button class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal4')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-100 transition-colors duration-150">
                    <td class="py-4 px-6 text-sm text-gray-700">José Antonio Martínez Sánchez</td>
                    <td class="py-4 px-6 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Monitoreo</span>
                    </td>
                    <td class="py-4 px-6 text-sm text-right">
                        <div class="inline-flex items-center space-x-3">
                            <button class="text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150" aria-label="Editar" onclick="editUser()">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-gray-500 hover:text-red-600 focus:outline-none transition duration-150" aria-label="Eliminar" onclick="openConfirmDeleteModal('deleteUserModal5')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Paginador -->
    <div class="mt-6 flex flex-col items-center">
        <div class="text-xs text-gray-600 mb-2">
            Mostrando <span id="startRecord">1</span> a <span id="endRecord">10</span> de <span id="totalRecords">50</span> registros
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 border border-transparent bg-blue-600 text-white text-xs font-medium rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="previousPage()" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>
            <select id="pageSelect" class="border border-gray-300 bg-white text-gray-700 text-xs rounded-full focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition ease-in-out duration-150" onchange="gotoPage(this.value)">
                <option value="1" selected>1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select>
            <button class="px-3 py-1 border border-transparent bg-blue-600 text-white text-xs font-medium rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="nextPage()" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Componentes Modal para Confirmar Eliminación para cada usuario -->
<x-modal-delete modalId="deleteUserModal1" formId="deleteUserForm1" />
<x-modal-delete modalId="deleteUserModal2" formId="deleteUserForm2" />
<x-modal-delete modalId="deleteUserModal3" formId="deleteUserForm3" />
<x-modal-delete modalId="deleteUserModal4" formId="deleteUserModal4" />
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

<script src="{{ asset('js/confirm-delete.js') }}"></script>

<script>
function editUser() {
    // Lógica para editar un usuario
}

let currentPage = 1;
const totalPages = 10; // Número total de páginas de ejemplo
const recordsPerPage = 10;
const totalRecords = 50; // Número total de registros de ejemplo

function updatePagination() {
    const startRecord = (currentPage - 1) * recordsPerPage + 1;
    const endRecord = Math.min(currentPage * recordsPerPage, totalRecords);
    
    document.getElementById('startRecord').textContent = startRecord;
    document.getElementById('endRecord').textContent = endRecord;
    document.getElementById('totalRecords').textContent = totalRecords;

    document.getElementById('pageSelect').value = currentPage;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
        // Añadir lógica para obtener y mostrar los datos de la nueva página
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
        // Añadir lógica para obtener y mostrar los datos de la nueva página
    }
}

function gotoPage(page) {
    currentPage = parseInt(page);
    updatePagination();
    // Añadir lógica para obtener y mostrar los datos de la nueva página
}

document.addEventListener('DOMContentLoaded', function () {
    updatePagination();
});

// Función para filtrar la tabla de usuarios
document.getElementById('userSearch').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let rows = document.getElementById('userTableBody').getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        let name = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        let role = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        if (name.indexOf(filter) > -1 || role.indexOf(filter) > -1) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
});
</script>

@endsection
