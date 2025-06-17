<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Movimientos de Inventario') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('inventory.index') }}')" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

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
                <div>
                    <label class="block text-sm">Producto</label>
                    <input type="text" name="product" value="{{ $filters['product'] ?? '' }}" class="form-input" @input.debounce.500ms="fetchTable()">
                </div>
            </form>
            <a href="{{ route('inventory.create') }}" class="btn-primary">
                Nueva Entrada
            </a>
            <a href="{{ route('inventory.createExit') }}" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600">
                Nueva Salida
            </a>
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
