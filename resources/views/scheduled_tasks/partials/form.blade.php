<div class="mb-4">
    <label for="task_name" class="block text-gray-700 font-bold mb-2">Nombre de la Tarea:</label>
    <input type="text" name="task_name" id="task_name" value="{{ old('task_name', $scheduledTask->task_name ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('task_name') border-red-500 @enderror">
    @error('task_name')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="frequency" class="block text-gray-700 font-bold mb-2">Frecuencia:</label>
    <select name="frequency" id="frequency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('frequency') border-red-500 @enderror">
        <option value="">Selecciona una frecuencia</option>
        <option value="Diaria" {{ old('frequency', $scheduledTask->frequency ?? '') == 'Diaria' ? 'selected' : '' }}>Diaria</option>
        <option value="Semanal" {{ old('frequency', $scheduledTask->frequency ?? '') == 'Semanal' ? 'selected' : '' }}>Semanal</option>
        <option value="Mensual" {{ old('frequency', $scheduledTask->frequency ?? '') == 'Mensual' ? 'selected' : '' }}>Mensual</option>
        <option value="Personalizada" {{ old('frequency', $scheduledTask->frequency ?? '') == 'Personalizada' ? 'selected' : '' }}>Personalizada</option>
    </select>
    @error('frequency')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<!-- Campos adicionales dependiendo de la frecuencia seleccionada -->
<div id="custom-fields">
    @include('scheduled_tasks.partials.daily')
    @include('scheduled_tasks.partials.weekly')
    @include('scheduled_tasks.partials.monthly')
    @include('scheduled_tasks.partials.custom')
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        function initializeFlatpickr() {
            $('.flatpickr:visible').flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
        }

        function showFieldsForFrequency(frequency) {
            $('#custom-fields > div').hide(); // Esconde todos los campos adicionales

            if (frequency == 'Diaria') {
                $('#daily-fields').show();
            } else if (frequency == 'Semanal') {
                $('#weekly-fields').show();
            } else if (frequency == 'Mensual') {
                $('#monthly-fields').show();
            } else if (frequency == 'Personalizada') {
                $('#custom-fields-content').show();
            }

            initializeFlatpickr(); // Inicializa Flatpickr solo en el campo visible
        }

        // Mostrar los campos correspondientes al cargar la página
        showFieldsForFrequency($('#frequency').val());

        // Cambiar los campos visibles cuando se cambia la frecuencia
        $('#frequency').change(function() {
            showFieldsForFrequency($(this).val());
        });
    });
</script>
@endpush
