<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lavador') }}: {{ $washer->name }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('washers.show', $washer) }}')" class="py-4 max-w-6xl mx-auto sm:px-6 lg:px-8">


        <div class="mb-4">
            <a href="{{ route('washers.index') }}" class="text-blue-600 hover:underline">&larr; Volver a lista</a>
        </div>

        <div class="mb-4 flex justify-end space-x-2">
            <a href="{{ route('washers.edit', $washer) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Editar</a>
            <form action="{{ route('washers.destroy', $washer) }}" method="POST" onsubmit="return confirm('¿Eliminar lavador?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
            </form>
            <button type="button" onclick="preparePayment({{ $washer->id }})" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Marcar Pago</button>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <p class="mb-4"><strong>Saldo pendiente:</strong> RD$ {{ number_format($pending, 2) }}</p>
            <form method="GET" x-ref="form" class="flex items-end gap-2 mb-4">
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
                    ">Ahora</button>
                </div>
            </form>
            <div x-html="tableHtml">
                @include('washers.partials.ledger', ['events' => $events])
            </div>
        </div>
    <x-modal name="pay-washer-{{ $washer->id }}" focusable>
        <form method="POST" action="{{ route('washers.pay', $washer) }}" class="p-6 space-y-4" x-data="{ paymentDate: '{{ now()->toDateString() }}' }">
            @csrf
            <input type="hidden" name="amount" value="0">
            <input type="hidden" name="total_washes" value="0">
            <h2 class="text-lg font-medium text-gray-900">Confirmar pago</h2>
            <p class="text-sm text-gray-600">Se pagará a <strong>{{ $washer->name }}</strong> RD$ <span class="selected-amount">0.00</span> en la fecha <span x-text="paymentDate"></span>.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mt-2">Fecha del pago</label>
                <input type="date" name="payment_date" x-model="paymentDate" class="form-input w-full" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Confirmar</x-primary-button>
            </div>
        </form>
    </x-modal>
    </div>
</x-app-layout>

<script>
function preparePayment(id) {
    const checks = document.querySelectorAll('.gain-check:checked');
    if (checks.length === 0) {
        alert('Seleccione al menos un registro para pagar.');
        return;
    }
    let total = 0;
    checks.forEach(c => total += parseFloat(c.dataset.amount));
    const modal = document.getElementById(`pay-washer-${id}`);
    const form = modal.querySelector('form');
    form.querySelector('input[name="amount"]').value = total.toFixed(2);
    form.querySelector('input[name="total_washes"]').value = checks.length;
    form.querySelector('.selected-amount').textContent = total.toFixed(2);
    window.dispatchEvent(new CustomEvent('open-modal', { detail: `pay-washer-${id}` }));
}
</script>
