<div id="custom-fields-content">
    <label for="custom_date" class="block text-gray-700 font-bold mb-2">Fecha:</label>
    <input type="date" name="custom_date" id="custom_date" value="{{ old('custom_date', $scheduledTask->custom_date ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-600 @error('custom_date') border-red-500 @enderror">
    @error('custom_date')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror

    <div id="custom-time-field">
        <x-time-picker 
            name="execution_time_custom" 
            id="execution_time_custom" 
            value="{{ old('execution_time_custom', $scheduledTask->execution_time ?? '') }}" 
        />
    </div>
</div>
