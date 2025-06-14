<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tickets Cancelados') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-end gap-4">
            <form method="GET" class="flex items-end gap-2" x-data>
                <div>
                    <label class="block text-sm">Desde</label>
                    <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="form-input" x-on:change="$root.submit()">
                </div>
                <div>
                    <label class="block text-sm">Hasta</label>
                    <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="form-input" x-on:change="$root.submit()">
                </div>
            </form>
            <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline">&laquo; Volver a activos</a>
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
                        <tr class="border-t">
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
        <div class="mt-4">
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
