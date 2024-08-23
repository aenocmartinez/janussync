<h2 class="text-lg font-semibold text-gray-700 mb-2">Detalle de Cursos</h2>
<table class="min-w-full text-sm box-border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">ID</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Nombre del Curso</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Código</th>
            <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Template ID</th>
        </tr>
    </thead>
    <tbody>
        @foreach($details as $detail)
            <tr class="border-b hover:bg-gray-50 transition-colors duration-300 box-border">
                <td class="p-2 md:p-4">{{ $detail->id }}</td>
                <td class="p-2 md:p-4">{{ $detail->course }}</td>
                <td class="p-2 md:p-4">{{ $detail->code }}</td>
                <td class="p-2 md:p-4">{{ $detail->TemplateId }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
