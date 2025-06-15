<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Caja Chica') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('petty-cash.index') }}')" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-end gap-4">
            <form method="GET" x-ref="form" class="flex items-end gap-2">
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
                        $refs.form.start.value = new Date().toISOString().slice(0,10);
                        $refs.form.end.value = new Date().toISOString().slice(0,10);
                        fetchTable();
                    ">Ahora</button>
                </div>
            </form>
            <a href="{{ route('petty-cash.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">Nuevo Gasto</a>
        </div>

        <div class="mb-4 text-sm text-gray-700">
            Gastado hoy: RD$ {{ number_format($todayTotal, 2) }} |
            Disponible hoy: RD$ {{ number_format($remaining, 2) }}
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
