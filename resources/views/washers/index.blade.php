<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lavadores') }}
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
                            const today = new Date().toISOString().slice(0,10);
                            $refs.form.start.value = today;
                            $refs.form.end.value = today;
                            fetchTable();
                        ">Hoy</button>
                    </div>
                </form>
                <a href="{{ route('washers.create') }}" class="btn-primary">Nuevo Lavador</a>
            </div>

            <div x-html="tableHtml"></div>
    </div>
</x-app-layout>
