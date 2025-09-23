<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('dashboard') }}')" class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
            </form>
            <button type="button" class="px-4 py-2 bg-gray-300 rounded" @click="const today = window.getLocalDateInputValue ? window.getLocalDateInputValue() : new Date().toISOString().slice(0,10); document.querySelector('[name=\'start\']').value = today; document.querySelector('[name=\'end\']').value = today; fetchTable();">Ahora</button>
            <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nuevo Ticket</a>
            <a href="{{ route('petty-cash.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Nuevo Gasto</a>
            <a href="{{ route('dashboard.download', request()->all()) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Descargar</a>
        </div>

        <div x-html="tableHtml">
            @include('dashboard.partials.summary', [
                'totalFacturado' => $totalFacturado,
                'cashTotal' => $cashTotal,
                'transferTotal' => $transferTotal,
                'bankAccountTotals' => $bankAccountTotals,
                'washerPayDue' => $washerPayDue,
                'serviceTotal' => $serviceTotal,
                'productTotal' => $productTotal,
                'drinkTotal' => $drinkTotal,
                'grossProfit' => $grossProfit,
                'lastExpenses' => $lastExpenses,
                'movements' => $movements,
                'pettyCashTotal' => $pettyCashTotal,
                'accountsReceivable' => $accountsReceivable,
                'pendingTickets' => $pendingTickets,
            ])
        </div>
    </div>
</x-app-layout>
