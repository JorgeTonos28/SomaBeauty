<div class="mb-4 bg-white p-4 shadow sm:rounded-lg">
    <p>Total adeudado: <strong>RD$ {{ number_format($pendingTotal, 2) }}</strong></p>
</div>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Pendiente</th>
                <th class="px-4 py-2">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($washers as $washer)
            <tr class="border-b cursor-pointer" ondblclick="window.location='{{ route('washers.show', ['washer' => $washer, 'start' => $filters['start'], 'end' => $filters['end']]) }}'">
                <td class="px-4 py-2">{{ $washer->name }}</td>
                <td class="px-4 py-2">RD$ {{ number_format($washer->range_pending, 2) }}</td>
                <td class="px-4 py-2">{{ $washer->active ? 'Activo' : 'Inactivo' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
