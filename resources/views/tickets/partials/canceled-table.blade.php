<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Cliente</th>
                <th class="border px-4 py-2">Facturaciones</th>
                <th class="border px-4 py-2">Total</th>
                <th class="border px-4 py-2">Pago</th>
                <th class="border px-4 py-2">Cambio</th>
                <th class="border px-4 py-2">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $ticket->id }}</td>
                    <td class="px-4 py-2">{{ $ticket->customer_name }}</td>
                    <td class="px-4 py-2">
                        {{ $ticket->details->pluck('type')->unique()->map(fn($t) => match($t){
                            'service' => 'Lavado', 'product' => 'Productos', 'drink' => 'Tragos'
                        })->implode(', ') }}
                    </td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->total_amount, 2) }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->paid_amount, 2) }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->change, 2) }}</td>
                    <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $tickets->withQueryString()->links() }}
</div>
