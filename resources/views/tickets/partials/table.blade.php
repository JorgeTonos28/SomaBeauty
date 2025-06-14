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
                <tr class="border-t cursor-pointer" x-on:click="selected = {{ $ticket->id }}" :class="selected === {{ $ticket->id }} ? 'bg-blue-100' : ''">
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
@foreach ($tickets as $ticket)
    <x-modal name="cancel-{{ $ticket->id }}" focusable>
        <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">¿Cancelar este ticket?</h2>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-danger-button class="ms-3">Confirmar</x-danger-button>
            </div>
        </form>
    </x-modal>
@endforeach
