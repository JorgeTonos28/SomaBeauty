<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">Cliente</th>
                <th class="border px-4 py-2">Facturaciones</th>
                <th class="border px-4 py-2">Descuento</th>
                <th class="border px-4 py-2">Total</th>
                <th class="border px-4 py-2">Cuenta</th>
                <th class="border px-4 py-2">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
                <tr class="border-t cursor-pointer {{ $ticket->pending ? 'bg-red-50' : '' }}"
                    x-on:click="
                        if (selected === {{ $ticket->id }}) {
                            selected = null; selectedPending = false;
                        } else {
                            selected = {{ $ticket->id }}; selectedPending = {{ $ticket->pending ? 'true' : 'false' }};
                        }
                    "
                    :class="selected === {{ $ticket->id }} ? (selectedPending ? 'bg-red-200' : 'bg-blue-100') : ''">
                    <td class="px-4 py-2">{{ $ticket->customer_name }}</td>
                    <td class="px-4 py-2">
                        {{ $ticket->details->pluck('type')->unique()->map(fn($t) => match($t){
                            'service' => 'Lavado', 'product' => 'Productos', 'drink' => 'Tragos'
                        })->implode(', ') }}
                    </td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->discount_total, 2) }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->total_amount, 2) }}</td>
                    <td class="px-4 py-2">
                        {{ optional($ticket->bankAccount)->bank ? $ticket->bankAccount->bank.' - '.$ticket->bankAccount->account : '' }}
                    </td>
                    <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $tickets->withQueryString()->links() }}
</div>
@foreach ($tickets as $ticket)
    <x-modal name="cancel-{{ $ticket->id }}" focusable>
        <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">¿Cancelar este ticket?</h2>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-danger-button class="ms-3">Confirmar</x-danger-button>
            </div>
        </form>
    </x-modal>
    @if($ticket->pending)
    <x-modal name="pay-{{ $ticket->id }}" focusable>
        <form method="POST" action="{{ route('tickets.pay', $ticket) }}" class="p-6 space-y-4" x-data="payForm({{ $ticket->total_amount }})">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Monto Pagado</label>
                <input type="number" name="paid_amount" step="0.01" x-model.number="paid" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="payment_method" x-model="method" class="form-select w-full" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="mixto">Mixto</option>
                </select>
            </div>
            <div x-show="method === 'transferencia'">
                <label class="block text-sm font-medium text-gray-700">Cuenta Bancaria</label>
                <select name="bank_account_id" class="form-select w-full">
                    <option value="">-- Seleccionar --</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->bank }} - {{ $acc->account }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-sm space-x-4">
                <span>Total: RD$ {{ number_format($ticket->total_amount,2) }}</span>
                <span>Cambio: RD$ <span x-text="formatCurrency(change)"></span></span>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Confirmar</x-primary-button>
            </div>
        </form>
    </x-modal>
    @endif
@endforeach
