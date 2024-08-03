<!-- resources/views/components/modal-delete.blade.php -->
@props(['modalId', 'formId', 'title' => 'Confirmar Eliminación', 'message' => '¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.'])

<div id="{{ $modalId }}" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $title }}</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">{{ $message }}</p>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" onclick="submitConfirmDeleteForm('{{ $formId }}')">
                    Eliminar
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeConfirmDeleteModal('{{ $modalId }}')">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
