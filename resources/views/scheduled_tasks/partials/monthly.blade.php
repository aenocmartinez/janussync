<div id="monthly-fields">
    <label for="day_of_month" class="block text-gray-700 font-bold mb-2">Día del Mes:</label>
    <input type="number" name="day_of_month" id="day_of_month" value="{{ old('day_of_month', $scheduledTask->day_of_month ?? '') }}" min="1" max="31" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

    <label for="execution_time" class="block text-gray-700 font-bold mb-2 mt-4">Hora de Ejecución:</label>
    <input type="time" name="execution_time" id="execution_time" value="{{ old('execution_time', $scheduledTask->execution_time ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>
