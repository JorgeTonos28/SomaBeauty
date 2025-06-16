<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 border">Fecha</th>
                <th class="px-4 py-2 border">Producto</th>
                <th class="px-4 py-2 border">Tipo</th>
                <th class="px-4 py-2 border">Cantidad</th>
                <th class="px-4 py-2 border">Concepto</th>
                <th class="px-4 py-2 border">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $move)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $move->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $move->product->name }}</td>
                    <td class="px-4 py-2">{{ ucfirst($move->movement_type) }}</td>
                    <td class="px-4 py-2">{{ $move->quantity }}</td>
                    <td class="px-4 py-2">{{ $move->concept }}</td>
                    <td class="px-4 py-2">{{ optional($move->user)->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $movements->withQueryString()->links() }}
</div>
