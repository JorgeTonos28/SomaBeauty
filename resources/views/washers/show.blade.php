<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estilista') }}: {{ $washer->name }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('washers.show', $washer) }}', { onUpdate(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const pending = doc.getElementById('pending-meta')?.dataset.pending;
        if (pending) {
            document.getElementById('washer-pending').textContent = parseFloat(pending).toFixed(2);
        }
    } })" class="py-4 max-w-6xl mx-auto sm:px-6 lg:px-8">


        <div class="mb-4">
            <a href="{{ route('washers.index') }}" class="text-blue-600 hover:underline">&larr; Volver a lista</a>
        </div>

        <div class="mb-4 flex justify-end space-x-2">
            <a href="{{ route('washers.edit', $washer) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Editar</a>
            <form action="{{ route('washers.destroy', $washer) }}" method="POST" onsubmit="return confirm('¿Eliminar estilista?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
            </form>
            <button type="button" onclick="preparePayment({{ $washer->id }})" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Marcar Pago</button>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <p class="mb-4"><strong>Saldo pendiente:</strong> RD$ <span id="washer-pending">{{ number_format($pending, 2) }}</span></p>
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
                @include('washers.partials.ledger', ['events' => $events, 'pending' => $pending])
            </div>
        </div>
    <x-modal name="pay-washer-{{ $washer->id }}" focusable>
            <form id="pay-washer-form-{{ $washer->id }}" method="POST" action="{{ route('washers.pay', $washer) }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="amount" value="0">
            <input type="hidden" name="total_washes" value="0">
            <input type="hidden" name="wash_ids" value="">
            <input type="hidden" name="movement_ids" value="">
            <h2 class="text-lg font-medium text-gray-900">Confirmar pago</h2>
            <p class="text-sm text-gray-600">Se pagará a <strong>{{ $washer->name }}</strong> RD$ <span class="selected-amount">0.00</span>.</p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Confirmar</x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="washer-pay-error">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Error</h2>
            <p class="mt-2 text-sm text-gray-600">Debe seleccionar al menos un registro para pagar.</p>
            <div class="mt-6 flex justify-end">
                <x-primary-button x-on:click="$dispatch('close')">Cerrar</x-primary-button>
            </div>
        </div>
    </x-modal>
    </div>
</x-app-layout>

<script>
function preparePayment(id) {
    const checks = document.querySelectorAll('.gain-check:checked');
    if (checks.length === 0) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'washer-pay-error' }));
        return;
    }
    let total = 0;
    const washIds = [];
    const movementIds = [];
    checks.forEach(c => {
        total += parseFloat(c.dataset.amount);
        if (c.dataset.wash) washIds.push(c.dataset.wash);
        if (c.dataset.movement) movementIds.push(c.dataset.movement);
    });
    const form = document.getElementById(`pay-washer-form-${id}`);
    form.querySelector('input[name="amount"]').value = total.toFixed(2);
    form.querySelector('input[name="total_washes"]').value = washIds.length;
    form.querySelector('input[name="wash_ids"]').value = washIds.join(',');
    form.querySelector('input[name="movement_ids"]').value = movementIds.join(',');
    form.querySelector('.selected-amount').textContent = total.toFixed(2);
    window.dispatchEvent(new CustomEvent('open-modal', { detail: `pay-washer-${id}` }));
}
</script>
