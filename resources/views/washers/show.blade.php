<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lavador') }}: {{ $washer->name }}
        </h2>
    </x-slot>

    <div class="py-4 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('washers.index') }}" class="text-blue-600 hover:underline">&larr; Volver a lista</a>
        </div>

        <div class="mb-4 flex justify-end space-x-2">
            <a href="{{ route('washers.edit', $washer) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Editar</a>
            <form action="{{ route('washers.destroy', $washer) }}" method="POST" onsubmit="return confirm('¿Eliminar lavador?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
            </form>
            <form action="{{ route('washers.pay', $washer) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Marcar Pago</button>
            </form>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <p class="mb-2"><strong>Saldo pendiente:</strong> RD$ {{ number_format($washer->pending_amount, 2) }}</p>
            @if($fromDate)
                <p class="mb-4 text-sm text-gray-600">Saldo desde {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</p>
            @endif
            <h3 class="font-semibold mb-2">Lavados recientes</h3>
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Vehículo</th>
                        <th class="px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $ticket->vehicleType->name }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($ticket->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
