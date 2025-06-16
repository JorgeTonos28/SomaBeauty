<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tickets Pendientes') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('tickets.pending') }}', {selected: null, selectedPending: false})" x-on:click.away="selected = null; selectedPending=false" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

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
            </form>
            <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline">&laquo; Volver a tickets</a>
        </div>
        <div class="mb-4 flex gap-4">
            <button x-show="selected" x-on:click="$dispatch('open-modal', 'cancel-' + selected)" class="text-red-600" title="Cancelar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <button x-show="selected && selectedPending" x-on:click="$dispatch('open-modal', 'pay-' + selected)" class="text-green-600" title="Pagar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M9 6h3.75M9 9h3.75" />
                </svg>
            </button>
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
