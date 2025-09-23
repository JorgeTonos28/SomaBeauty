<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Caja Chica') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('petty-cash.index') }}', { showFundModal: false })" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">


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
                        const today = window.getLocalDateInputValue ? window.getLocalDateInputValue() : new Date().toISOString().slice(0,10);
                        $refs.form.start.value = today;
                        $refs.form.end.value = today;
                        fetchTable();
                    ">Ahora</button>
                </div>
            </form>
            <a href="{{ route('petty-cash.create') }}" class="btn-primary">Nuevo Gasto</a>
            @if(Auth::user()->role === 'admin')
            <button type="button" class="btn-secondary" @click="showFundModal = true">Configurar Monto</button>
            @endif
        </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('success'))
            <div class="mb-4 text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4 text-sm text-gray-700">
            Monto diario: RD$ {{ number_format($pettyCashAmount, 2) }} |
            Gastado hoy: RD$ {{ number_format($todayTotal, 2) }} |
            Disponible hoy: RD$ {{ number_format($remaining, 2) }}
        </div>

        <div x-html="tableHtml"></div>

        @if(Auth::user()->role === 'admin')
        <div x-show="showFundModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow w-80" @click.away="showFundModal=false">
                <h3 class="text-lg font-semibold mb-4">Configurar Caja Chica</h3>
                <form method="POST" action="{{ route('petty-cash.update-fund') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm">Monto</label>
                        <input type="number" step="0.01" name="amount" value="{{ $pettyCashAmount }}" class="form-input w-full" required>
                    </div>
                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" name="apply_today" value="1" checked class="form-checkbox">
                        <span class="text-sm">Aplicar desde hoy</span>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="px-3 py-1 bg-gray-300 rounded" @click="showFundModal=false">Cancelar</button>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
