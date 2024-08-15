<div id="daily-fields">
    <label for="execution_time" class="block text-gray-700 font-bold mb-2">Hora de Ejecución:</label>
    <input type="time" name="execution_time" id="execution_time" value="{{ old('execution_time', $scheduledTask->execution_time ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>
