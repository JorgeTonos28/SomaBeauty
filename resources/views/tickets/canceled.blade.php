<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tickets Cancelados') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('tickets.canceled') }}', {selected: null})" x-on:click.away="selected = null" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

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
            <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline">&laquo; Volver a activos</a>
            <button x-show="selected" x-on:click="$dispatch('open-modal', 'view-' + selected)" class="text-gray-600" title="Ver">
                <i class="fa-solid fa-eye fa-lg"></i>
            </button>
        </div>

        <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
