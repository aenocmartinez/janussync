<div class="mb-4">
    <label for="{{ $id ?? 'execution_time' }}" class="block text-gray-700 font-bold mb-2">{{ $label ?? 'Hora de Ejecución:' }}</label>
    <input type="text" name="{{ $name ?? 'execution_time' }}" id="{{ $id ?? 'execution_time' }}" value="{{ $value ?? old($name ?? 'execution_time') }}" class="flatpickr w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-600 @error($name ?? 'execution_time') border-red-500 @enderror">
    @error($name ?? 'execution_time')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
