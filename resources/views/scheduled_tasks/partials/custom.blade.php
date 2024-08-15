<div id="custom-fields-content">
    <label for="custom_date" class="block text-gray-700 font-bold mb-2">Fecha:</label>
    <input type="date" name="custom_date" id="custom_date" value="{{ old('custom_date', $scheduledTask->custom_date ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

    <label for="custom_time" class="block text-gray-700 font-bold mb-2 mt-4">Hora de Ejecución:</label>
    <input type="time" name="custom_time" id="custom_time" value="{{ old('custom_time', $scheduledTask->custom_time ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>
