<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Descuento') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white p-4 rounded shadow">
            <div><strong>Elemento:</strong> {{ $discount->discountable->name ?? '' }}</div>
            <div><strong>Tipo:</strong> {{ $discount->discountable_type === App\Models\Product::class ? 'Producto' : 'Servicio' }}</div>
            <div><strong>Descuento:</strong>
                @if($discount->amount_type === 'fixed')
                    RD${{ number_format($discount->amount,2) }}
                @else
                    {{ $discount->amount }}%
                @endif
            </div>
            <div><strong>Fin:</strong> {{ optional($discount->end_at)->format('d/m/Y H:i') }}</div>
            <div><strong>Estado:</strong> {{ $discount->active ? 'Activo' : 'Inactivo' }}</div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Historial</h3>
            <ul class="list-disc pl-4">
                @foreach($discount->logs as $log)
                    <li>
                        {{ $log->created_at->format('d/m/Y H:i') }} - {{ $log->user->name }} ({{ $log->action }})
                        @if($log->amount)
                            - {{ $log->amount_type === 'fixed' ? 'RD$'.number_format($log->amount,2) : $log->amount.'%' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
