<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Descuento') }}
        </h2>
    </x-slot>

    <div class="pb-4">
        <a href="{{ route('discounts.index') }}" class="text-blue-500 hover:underline">&larr; Volver</a>
    </div>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white p-4 rounded shadow">
            <div><strong>Elemento:</strong> {{ $discount->discountable->name ?? '' }}</div>
            <div><strong>Tipo:</strong>
                @if($discount->discountable_type === App\Models\Product::class)
                    Producto
                @elseif($discount->discountable_type === App\Models\Drink::class)
                    Trago
                @else
                    Servicio
                @endif
            </div>
            <div><strong>Descuento:</strong>
                @if($discount->amount_type === 'fixed')
                    RD${{ number_format($discount->amount,2) }}
                @else
                    {{ $discount->amount }}%
                @endif
            </div>
            @php
                $price = null;
                if($discount->discountable_type === App\Models\Product::class) $price = $discount->discountable->price;
                elseif($discount->discountable_type === App\Models\Drink::class) $price = $discount->discountable->price;
                elseif($discount->discountable_type === App\Models\Service::class) $price = optional($discount->discountable->prices->first())->price;
                if($price !== null){
                    $disc = $discount->amount_type === 'fixed' ? $discount->amount : $price * $discount->amount/100;
                    $final = max(0, $price - $disc);
                }
            @endphp
            @if(isset($final))
                <div><strong>Precio con descuento:</strong> RD${{ number_format($final,2) }}</div>
            @endif
            <div><strong>Inicio:</strong> {{ optional($discount->start_at)->format('d/m/Y h:i A') }}</div>
            <div><strong>Fin:</strong> {{ optional($discount->end_at)->format('d/m/Y h:i A') }}</div>
            <div><strong>Estado:</strong> {{ $discount->active ? 'Activo' : 'Inactivo' }}</div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Historial</h3>
            <ul class="list-disc pl-4">
                @foreach($discount->logs as $log)
                    <li>
                        {{ $log->created_at->format('d/m/Y h:i A') }} - {{ $log->user->name }} ({{ $log->action }})
                        @if($log->amount)
                            - {{ $log->amount_type === 'fixed' ? 'RD$'.number_format($log->amount,2) : $log->amount.'%' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
