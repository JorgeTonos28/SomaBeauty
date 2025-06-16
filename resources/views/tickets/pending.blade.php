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
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
            <button x-show="selected && selectedPending" x-on:click="$dispatch('open-modal', 'pay-' + selected)" class="text-green-600" title="Pagar">
                <i class="fa-solid fa-money-bill-wave fa-lg"></i>
            </button>
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
