<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden max-h-96 overflow-y-auto">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Cliente</th>
                <th class="border px-4 py-2">Facturaciones</th>
                <th class="border px-4 py-2">Descuento</th>
                <th class="border px-4 py-2">Total</th>
                <th class="border px-4 py-2">Concepto</th>
                <th class="border px-4 py-2">Cuenta</th>
                <th class="border px-4 py-2">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
                <tr class="border-t cursor-pointer" x-on:click="selected === {{ $ticket->id }} ? selected = null : selected = {{ $ticket->id }}" :class="selected === {{ $ticket->id }} ? 'bg-blue-100' : ''">
                    <td class="px-4 py-2">{{ $ticket->id }}</td>
                    <td class="px-4 py-2">{{ $ticket->customer_name }}</td>
                    <td class="px-4 py-2">
                        {{ $ticket->details->pluck('type')->unique()->map(fn($t) => match($t){
                            'service' => 'Lavado', 'product' => 'Productos', 'drink' => 'Tragos'
                        })->implode(', ') }}
                    </td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->discount_total, 2) }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($ticket->total_amount, 2) }}</td>
                    <td class="px-4 py-2">{{ $ticket->cancel_reason }}</td>
                    <td class="px-4 py-2">
                        {{ optional($ticket->bankAccount)->bank ? $ticket->bankAccount->bank.' - '.$ticket->bankAccount->account : '' }}
                    </td>
                    <td class="px-4 py-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@foreach ($tickets as $ticket)
    <x-modal name="view-{{ $ticket->id }}" focusable>
        <div class="p-6 space-y-4 text-sm">
            <p><strong>Cliente:</strong> {{ $ticket->customer_name }}</p>
            <p><strong>Fecha:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
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
            <div>
                <h3 class="font-semibold mb-1">Detalles</h3>
                <ul class="list-disc ps-5 space-y-1">
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
            <p><strong>Descuento:</strong> RD$ {{ number_format($ticket->discount_total, 2) }}</p>
            <p><strong>Total:</strong> RD$ {{ number_format($ticket->total_amount, 2) }}</p>
            <p><strong>Concepto:</strong> {{ $ticket->cancel_reason }}</p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
            </div>
        </div>
    </x-modal>
@endforeach
