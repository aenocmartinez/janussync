<table class="min-w-full text-sm">
    <thead>
        <tr>
            <th class="p-2 text-left">Curso</th>
            <th class="p-2 text-left">Código</th>
            <th class="p-2 text-left">Detalle</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($details as $detail)
            <tr>
                <td class="p-2">{{ $detail->course }}</td>
                <td class="p-2">{{ $detail->code }}</td>
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
