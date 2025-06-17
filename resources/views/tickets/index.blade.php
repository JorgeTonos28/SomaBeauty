<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Facturación / Tickets') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('tickets.index') }}', {selected: null, selectedPending: false, selectedNoWasher: false, pending: {{ $filters['pending'] ?? 'null' }}})" x-on:click.away="selected = null; selectedPending=false; selectedNoWasher=false" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-end gap-4">
            <form method="GET" x-ref="form" class="flex items-end gap-2">
                <input type="hidden" name="pending" x-model="pending">
                <div>
                    <label class="block text-sm">Desde</label>
                    <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="form-input" @change="fetchTable()">
                </div>
                <div>
                    <label class="block text-sm">Hasta</label>
                    <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="form-input" @change="fetchTable()">
                </div>
            </form>
            <button type="button" class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-600" @click="pending = 1; fetchTable()">Pendientes</button>
            <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="pending = null; fetchTable()">Todos</button>
            <a href="{{ route('tickets.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">Nuevo Ticket</a>
            <a href="{{ route('tickets.canceled') }}" class="text-blue-600 hover:underline">Ver cancelados</a>
            <button x-show="selected" x-on:click="$dispatch('open-modal', 'cancel-' + selected)" class="text-red-600" title="Cancelar">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
            <button x-show="selected && selectedPending" x-on:click="$dispatch('open-modal', 'pay-' + selected)" class="text-green-600" title="Pagar">
                <i class="fa-solid fa-money-bill-wave fa-lg"></i>
            </button>
            <button x-show="selected" x-on:click="$dispatch('open-modal', 'view-' + selected)" class="text-gray-600" title="Ver">
                <i class="fa-solid fa-eye fa-lg"></i>
            </button>
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
