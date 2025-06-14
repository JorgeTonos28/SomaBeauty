<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Facturación / Tickets') }}
        </h2>
    </x-slot>

    <div x-data="{selected: null}" x-on:click.away="selected = null" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex items-center gap-4">
            <a href="{{ route('tickets.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">Nuevo Ticket</a>
            <a href="{{ route('tickets.canceled') }}" class="text-blue-600 hover:underline">Ver cancelados</a>
            <button x-show="selected" x-on:click="window.location='{{ url('tickets') }}/' + selected + '/edit'" class="text-yellow-600" title="Editar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 113 3L6.75 19.5H3v-3.75L16.862 3.487z" />
                </svg>
            </button>
            <button x-show="selected" x-on:click="$dispatch('open-modal', 'cancel-' + selected)" class="text-red-600" title="Cancelar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

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
                        <tr class="border-t cursor-pointer" x-on:click="selected = {{ $ticket->id }}" :class="selected === {{ $ticket->id }} ? 'bg-blue-100' : ''">
                            <td class="px-4 py-2">{{ $ticket->id }}</td>
                            <td class="px-4 py-2">{{ $ticket->vehicleType->name }}</td>
                            <td class="px-4 py-2">{{ $ticket->washer->name }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($ticket->total_amount, 2) }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($ticket->paid_amount, 2) }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($ticket->change, 2) }}</td>
                            <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
    </div>
</x-app-layout>
