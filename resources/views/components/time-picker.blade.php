<div class="mb-4">
    <label for="{{ $id ?? 'execution_time' }}" class="block text-gray-700 font-bold mb-2">{{ $label ?? 'Hora de Ejecución:' }}</label>
    <input type="time" name="{{ $name ?? 'execution_time' }}" id="{{ $id ?? 'execution_time' }}" value="{{ $value ?? old($name ?? 'execution_time') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-600">
</div>

<style>
    input[type="time"] {
        appearance: none;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        color: #495057;
        transition: border-color 0.15s ease-in-out, background-color 0.15s ease-in-out;
    }

    input[type="time"]:focus {
        border-color: #6c757d;
        background-color: #e9ecef;
        outline: none;
    }
</style>
