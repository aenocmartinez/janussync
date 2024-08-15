<div id="daily-fields" style="display: none;">
    <x-time-picker 
        name="execution_time_daily" 
        id="execution_time_daily" 
        value="{{ old('execution_time_daily', $scheduledTask->execution_time ?? '') }}" 
    />
    @error('execution_time_daily')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
