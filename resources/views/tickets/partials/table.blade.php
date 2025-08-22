<div class="mb-4 bg-white p-4 shadow sm:rounded-lg">
    Total facturado: <strong>RD$ {{ number_format($invoicedTotal,2) }}</strong>
</div>
<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden max-h-96 overflow-y-auto">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">ID</th>
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
                <tr class="border-t cursor-pointer {{ $ticket->pending ? 'bg-red-100' : (!$ticket->washer_id && $ticket->details->where('type','service')->count() ? 'bg-orange-100' : '') }}"
                    x-on:click="
                        if (selected === {{ $ticket->id }}) {
                            selected = null; selectedPending = false; selectedNoWasher = false; selectedCreated = null;
                        } else {
                            selected = {{ $ticket->id }}; selectedPending = {{ $ticket->pending ? 'true' : 'false' }}; selectedNoWasher = {{ (!$ticket->washer_id && $ticket->details->where('type','service')->count()) ? 'true' : 'false' }}; selectedCreated = '{{ $ticket->created_at }}';
                        }
                    "
                    :class="selected === {{ $ticket->id }} ? (selectedPending ? 'bg-red-300' : (selectedNoWasher ? 'bg-orange-300' : 'bg-blue-100')) : ''">
                    <td class="px-4 py-2">{{ $ticket->id }}</td>
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
                    <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    @foreach ($tickets as $ticket)
        <x-modal name="cancel-{{ $ticket->id }}" focusable>
        <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" class="p-6 space-y-4">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">¿Cancelar este ticket?</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700">Concepto de cancelación</label>
                <input type="text" name="cancel_reason" class="form-input w-full" required>
            </div>
            <div class="flex justify-end">
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
    <x-modal name="view-{{ $ticket->id }}" focusable>
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="text-sm space-y-1">
                <p><strong>Cliente:</strong> {{ $ticket->customer_name }}</p>
                @if($ticket->customer_phone)
                    <p><strong>Teléfono:</strong> {{ $ticket->customer_phone }}</p>
                @endif
                <p><strong>Fecha:</strong> {{ $ticket->created_at->format('d/m/Y h:i A') }}</p>
                @if($ticket->vehicle)
                    <p><strong>Placa:</strong> {{ $ticket->vehicle->plate }}</p>
                    <p><strong>Marca:</strong> {{ $ticket->vehicle->brand }}</p>
                    <p><strong>Modelo:</strong> {{ $ticket->vehicle->model }}</p>
                    <p><strong>Color:</strong> {{ $ticket->vehicle->color }}</p>
                    @if($ticket->vehicle->year)
                        <p><strong>Año:</strong> {{ $ticket->vehicle->year }}</p>
                    @endif
                @endif
                @if($ticket->vehicleType)
                    <p><strong>Tipo de Vehículo:</strong> {{ $ticket->vehicleType->name }}</p>
                @endif
            </div>
            <div>
                <h3 class="font-semibold text-sm mb-1">Detalles</h3>
                <ul class="text-sm list-disc ps-5 space-y-1">
                    @foreach($ticket->details as $d)
                        <li>
                            {{ match($d->type){
                                'service' => $d->service->name ?? 'Servicio',
                                'product' => $d->product->name ?? 'Producto',
                                'drink' => $d->drink->name ?? 'Trago'
                            } }} x{{ $d->quantity }} - RD$ {{ number_format($d->unit_price,2) }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="text-sm space-y-1">
                <p><strong>Descuento:</strong> RD$ {{ number_format($ticket->discount_total, 2) }}</p>
                <p><strong>Total:</strong> RD$ {{ number_format($ticket->total_amount, 2) }}</p>
            </div>
            @if($ticket->washes->count())
                <div class="space-y-2">
                    @foreach($ticket->washes as $wash)
                        <div class="border rounded p-2">
                            <p class="text-sm font-semibold">{{ $wash->vehicle->brand }} | {{ $wash->vehicle->model }} | {{ $wash->vehicle->color }} | {{ $wash->vehicle->year }} | {{ $wash->vehicleType->name }}</p>
                            <p class="text-sm">Servicios: {{ $wash->details->where('type','service')->map(fn($d)=>$d->service->name)->implode(', ') }}</p>
                            <div class="mt-1">
                                <label class="block text-sm font-medium text-gray-700">Lavador</label>
                                <select name="washers[{{ $wash->id }}]" class="form-select w-full">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($washers as $w)
                                        <option value="{{ $w->id }}" {{ $w->id == $wash->washer_id ? 'selected' : '' }}>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div x-data="{method: '{{ $ticket->payment_method }}'}">
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="payment_method" x-model="method" class="form-select w-full">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="mixto">Mixto</option>
                </select>
                <div x-show="method === 'transferencia'" class="mt-2">
                    <label class="block text-sm font-medium text-gray-700">Cuenta Bancaria</label>
                    <select name="bank_account_id" class="form-select w-full">
                        <option value="">-- Seleccionar --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->id == $ticket->bank_account_id ? 'selected' : '' }}>{{ $acc->bank }} - {{ $acc->account }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
@endforeach
