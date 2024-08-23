<table class="min-w-full text-sm">
    <thead>
        <tr>
            <th class="p-2 text-left">Course ID</th>
            <th class="p-2 text-left">User ID</th>
            <th class="p-2 text-left">Grade</th>
            <th class="p-2 text-left">Term Number</th>
            <th class="p-2 text-left">Details</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($details as $detail)
            <tr>
                <td class="p-2">{{ $detail->course_id }}</td>
                <td class="p-2">{{ $detail->user_id }}</td>
                <td class="p-2">{{ $detail->grade }}</td>
                <td class="p-2">{{ $detail->term_number }}</td>
                <td class="p-2">
                    @php
                        $log = $detail->logTasks->first();
                    @endphp
                    @if($log)
                        @if(empty($log->details))
                            <span class="text-green-500">Registro exitoso</span>
                        @else
                            <span class="text-red-500">{{ $log->details }}</span>
                        @endif
                    @else
                        <span class="text-gray-500">No hay detalles disponibles</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
