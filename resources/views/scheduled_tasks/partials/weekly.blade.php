<div id="weekly-fields">
    <label for="day_of_week" class="block text-gray-700 font-bold mb-2">Día de la Semana:</label>
    <select name="day_of_week" id="day_of_week" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-600 @error('day_of_week') border-red-500 @enderror">
        <option value="">Selecciona un día</option>
        <option value="Lunes" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Lunes' ? 'selected' : '' }}>Lunes</option>
        <option value="Martes" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Martes' ? 'selected' : '' }}>Martes</option>
        <option value="Miércoles" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Miércoles' ? 'selected' : '' }}>Miércoles</option>
        <option value="Jueves" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Jueves' ? 'selected' : '' }}>Jueves</option>
        <option value="Viernes" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Viernes' ? 'selected' : '' }}>Viernes</option>
        <option value="Sábado" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Sábado' ? 'selected' : '' }}>Sábado</option>
        <option value="Domingo" {{ old('day_of_week', $scheduledTask->day_of_week ?? '') == 'Domingo' ? 'selected' : '' }}>Domingo</option>
    </select>
    @error('day_of_week')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror

    <div id="weekly-fields">
        <x-time-picker name="execution_time_weekly" id="execution_time_weekly" />
    </div>
    
</div>