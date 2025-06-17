<div class="space-y-4">
    <div class="bg-white p-4 shadow sm:rounded-lg flex justify-around items-center">
        <div class="text-center">
            <p class="text-lg">Total facturado</p>
            <p class="text-3xl font-bold">RD$ {{ number_format($totalFacturado, 2) }}</p>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="text-center">
            <p class="text-lg">Beneficio bruto</p>
            <p class="text-3xl font-bold">RD$ {{ number_format($grossProfit, 2) }}</p>
        </div>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white p-4 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Cuentas por cobrar</h3>
            <p>Total: <strong>RD$ {{ number_format($accountsReceivable, 2) }}</strong></p>
            <table class="min-w-full mt-2 table-auto text-sm border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border px-2 py-1">Fecha</th>
                        <th class="border px-2 py-1">Cliente</th>
                        <th class="border px-2 py-1">Tipo</th>
                        <th class="border px-2 py-1">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingTickets as $t)
                        <tr>
                            <td class="border px-2 py-1">{{ $t->created_at->format('d/m H:i') }}</td>
                            <td class="border px-2 py-1">{{ $t->customer_name }}</td>
                            <td class="border px-2 py-1">
                                {{ $t->details->pluck('type')->unique()->map(fn($tt) => match($tt){
                                    'service' => 'Lavado',
                                    'product' => 'Producto',
                                    'drink' => 'Tragos'
                                })->implode(', ') }}
                            </td>
                            <td class="border px-2 py-1 text-right">RD$ {{ number_format($t->total_amount,2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-white p-4 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Resumen</h3>
            <p>Efectivo: <strong>RD$ {{ number_format($cashTotal, 2) }}</strong></p>
            <p>Transferencias: <strong>RD$ {{ number_format($transferTotal, 2) }}</strong></p>
            <p>Caja chica: <strong>RD$ 3,200.00</strong></p>
            <p>Gastos de caja chica: <strong>RD$ {{ number_format($pettyCashTotal, 2) }}</strong></p>
            <p>Para lavadores: <strong>RD$ {{ number_format($washerPayDue, 2) }}</strong></p>
            <p>Ventas de lavados: RD$ {{ number_format($serviceTotal, 2) }}</p>
            <p>Ventas de productos: RD$ {{ number_format($productTotal, 2) }}</p>
            <p>Ventas de tragos: RD$ {{ number_format($drinkTotal, 2) }}</p>
            @if(Auth::user()->role === 'admin')
                <!-- Beneficio bruto se muestra en la sección superior -->
            @endif
        </div>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white p-4 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Últimos gastos de caja chica</h3>
            <ul class="list-disc ms-6">
                @foreach($lastExpenses as $expense)
                    <li>{{ $expense->created_at->format('d/m H:i') }} - {{ $expense->description }} (RD$ {{ number_format($expense->amount,2) }})</li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white p-4 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Transferencias Bancarias</h3>
            <p>Total: <strong>RD$ {{ number_format($transferTotal, 2) }}</strong></p>
            <ul class="list-disc ms-6">
                @foreach($bankAccountTotals as $acc)
                    <li>{{ $acc->bankAccount->bank }} - {{ $acc->bankAccount->account }}: RD$ {{ number_format($acc->total, 2) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div>
        <h3 class="text-lg font-semibold mb-2">Movimientos</h3>
        @include('dashboard.partials.movements-table', ['movements' => $movements])
    </div>
</div>
