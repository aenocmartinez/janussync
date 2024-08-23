<h2 class="text-lg font-semibold text-gray-700 mb-2">Detalle de Usuarios</h2>
<table class="min-w-full text-sm box-border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">ID</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Nombre</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Apellido</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach($details as $detail)
            <tr class="border-b hover:bg-gray-50 transition-colors duration-300 box-border">
                <td class="p-2 md:p-4">{{ $detail->id }}</td>
                <td class="p-2 md:p-4">{{ $detail->first_name }}</td>
                <td class="p-2 md:p-4">{{ $detail->last_name }}</td>
                <td class="p-2 md:p-4">{{ $detail->email }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
