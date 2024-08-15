<div id="daily-fields" style="display: none;">
    <label for="execution_time_daily" class="block text-gray-700 font-bold mb-2">Hora de Ejecución:</label>
    <x-time-picker 
        name="execution_time_daily" 
        id="execution_time_daily" 
        value="{{ old('execution_time_daily', $scheduledTask->execution_time ?? '') }}" 
    />
    @error('execution_time_daily')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
