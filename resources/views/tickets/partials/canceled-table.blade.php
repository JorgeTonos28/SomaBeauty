<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Vehículo</th>
                <th class="border px-4 py-2">Lavador</th>
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
                    <td class="px-4 py-2">{{ optional($ticket->vehicleType)->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ optional($ticket->washer)->name ?? '-' }}</td>
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
