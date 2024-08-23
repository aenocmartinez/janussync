<table class="min-w-full text-sm">
    <thead>
        <tr>
            <th class="p-2 text-left">Nombres</th>
            <th class="p-2 text-left">Apellidos</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Detalle</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($details as $detail)
            <tr>
                <td class="p-2">{{ $detail->first_name }}</td>
                <td class="p-2">{{ $detail->last_name }}</td>
                <td class="p-2">{{ $detail->email }}</td>
                <td class="p-2">
                    @foreach ($detail->logTasks as $log)
                        <div>
                            <strong>{{ $log->executed_at->format('d/m/Y H:i:s') }}:</strong> 
                            {{ $log->details }}
                        </div>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
