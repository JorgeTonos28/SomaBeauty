<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">Fecha</th>
                <th class="border px-4 py-2">Descripción</th>
                <th class="border px-4 py-2">Monto</th>
                <th class="border px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $expense->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $expense->description }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($expense->amount, 2) }}</td>
                    <td class="px-4 py-2">
                        <form action="{{ route('petty-cash.destroy', $expense) }}" method="POST" onsubmit="return confirm('¿Eliminar este gasto?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
