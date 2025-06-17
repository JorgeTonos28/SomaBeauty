<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lavador') }}: {{ $washer->name }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('washers.show', $washer) }}')" class="py-4 max-w-4xl mx-auto sm:px-6 lg:px-8">
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
            <p class="mb-4"><strong>Saldo pendiente:</strong> RD$ {{ number_format($washer->pending_amount, 2) }}</p>
            <form method="GET" x-ref="form" class="flex items-end gap-2 mb-4">
                <div>
                    <label class="block text-sm">Desde</label>
                    <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="form-input" @change="fetchTable()">
                </div>
                <div>
                    <label class="block text-sm">Hasta</label>
                    <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="form-input" @change="fetchTable()">
                </div>
                <div class="flex items-end">
                    <button type="button" class="px-3 py-2 bg-gray-200 rounded" @click="
                        const today = new Date().toISOString().slice(0,10);
                        $refs.form.start.value = today;
                        $refs.form.end.value = today;
                        fetchTable();
                    ">Ahora</button>
                </div>
            </form>
            <div x-html="tableHtml">
                @include('washers.partials.ledger', ['events' => $events])
            </div>
        </div>
    </div>
</x-app-layout>
