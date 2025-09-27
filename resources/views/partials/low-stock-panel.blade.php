@if($products->isNotEmpty())
<div class="mb-6 border border-yellow-300 bg-yellow-50 text-yellow-800 rounded p-4 space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold">Productos con stock bajo</h3>
            <p class="text-sm text-yellow-700">Estos productos están en o por debajo del mínimo configurado.</p>
        </div>
    </div>
    <div class="grid gap-3 md:grid-cols-2">
        @foreach($products as $product)
            <div class="flex items-center justify-between bg-white border border-yellow-200 rounded p-3">
                <div>
                    <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                    <p class="text-xs text-gray-600">
                        Stock actual: {{ $product->stock }} |
                        Mínimo:
                        @if($product->low_stock_threshold)
                            {{ $product->low_stock_threshold }}
                        @elseif($product->effective_low_stock_threshold)
                            Predeterminado ({{ $product->effective_low_stock_threshold }})
                        @else
                            —
                        @endif
                    </p>
                </div>
                <a href="{{ route('inventory.create', ['product_id' => $product->id]) }}" class="px-3 py-2 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700">Registrar entrada</a>
            </div>
        @endforeach
    </div>
</div>
@endif
