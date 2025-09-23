<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estilistas') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('washers.index') }}')" class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-wrap items-end gap-4 mb-4">
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
                        ">Hoy</button>
                    </div>
                </form>
                <div class="flex items-center gap-3 ml-auto">
                    <span class="text-sm text-gray-700">Comisión por defecto: <strong>{{ number_format($commissionRate, 2) }}%</strong></span>
                    <button type="button" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'commission-rate-settings' }))">Actualizar comisión</button>
                    <a href="{{ route('washers.create') }}" class="btn-primary">Agregar Estilista</a>
                </div>
            </div>

            <div x-html="tableHtml"></div>
        <x-modal name="commission-rate-settings" focusable>
            <form method="POST" action="{{ route('washers.updateCommissionRate') }}" class="p-6 space-y-4">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Actualizar porcentaje de comisión</h2>
                <p class="text-sm text-gray-600">Este porcentaje se aplicará a los nuevos tickets que se creen.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Porcentaje (%)</label>
                    <input type="number" name="commission_percentage" min="0" max="100" step="0.01" value="{{ number_format($commissionRate, 2, '.', '') }}" class="form-input w-full mt-1" required>
                </div>
                <div class="flex justify-end gap-2">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
