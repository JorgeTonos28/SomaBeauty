<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Productos del Kiosko') }}
        </h2>
    </x-slot>

    @php
        $defaultMinimumStock = optional($appSettings)->default_minimum_stock ?? \App\Models\AppSetting::DEFAULT_MINIMUM_STOCK;
    @endphp
    <div x-data="filterTable('{{ route('products.index') }}', {selected: null})" x-on:click.away="selected = null" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">


        @isset($lowStockProducts)
            @include('partials.low-stock-panel', ['products' => $lowStockProducts])
        @endisset

        @if (auth()->user()->role === 'admin')
            <div class="mb-4 flex items-center gap-4">
                <a href="{{ route('products.create') }}" class="btn-primary">
                    Nuevo Producto
                </a>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'edit-' + selected)" class="text-yellow-600" title="Editar">
                    <i class="fa-solid fa-pen fa-lg"></i>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'delete-' + selected)" class="text-red-600" title="Eliminar">
                    <i class="fa-solid fa-trash fa-lg"></i>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'entry-' + selected)" class="text-blue-600" title="Entrada">
                    <i class="fa-solid fa-plus fa-lg"></i>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'exit-' + selected)" class="text-purple-600" title="Salida">
                    <i class="fa-solid fa-minus fa-lg"></i>
                </button>
            </div>
        @endif

        <form method="GET" x-ref="form" class="mb-4">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar producto" class="form-input" @input.debounce.500ms="fetchTable()">
        </form>

        <div x-html="tableHtml"></div>
        @foreach ($products as $product)
            <x-modal name="edit-{{ $product->id }}" focusable>
                <form method="POST" action="{{ route('products.update', $product) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Nombre</label>
                        <input type="text" name="name" value="{{ $product->name }}" required class="form-input w-full">
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                        <input type="number" step="0.01" name="price" value="{{ $product->price }}" required class="form-input w-full">
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Aviso de escasez</label>
                        <input type="number" name="low_stock_threshold" min="0" value="{{ $product->low_stock_threshold }}" class="form-input w-full" placeholder="Opcional">
                        <p class="mt-1 text-xs text-gray-500">Si lo dejas vacío usaremos el valor general de {{ $defaultMinimumStock }} unidades.</p>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ms-3">Actualizar</x-primary-button>
                    </div>
                </form>
            </x-modal>

            <x-modal name="delete-{{ $product->id }}" focusable>
                <form method="POST" action="{{ route('products.destroy', $product) }}" class="p-6">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-lg font-medium text-gray-900">¿Eliminar este producto?</h2>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-danger-button class="ms-3">Eliminar</x-danger-button>
                    </div>
                </form>
            </x-modal>

            <x-modal name="entry-{{ $product->id }}" focusable>
                <form method="POST" action="{{ route('inventory.store') }}" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Cantidad</label>
                        <input type="number" name="quantity" min="1" required class="form-input w-full">
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ms-3">Guardar</x-primary-button>
                    </div>
                </form>
            </x-modal>

            <x-modal name="exit-{{ $product->id }}" focusable>
                <form method="POST" action="{{ route('inventory.storeExit') }}" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div>
                        <p class="text-sm text-gray-600">Stock actual: {{ $product->stock }}</p>
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Cantidad</label>
                        <input type="number" name="quantity" min="1" required class="form-input w-full">
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Concepto</label>
                        <input type="text" name="concept" required class="form-input w-full">
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ms-3">Guardar</x-primary-button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    </div>
</x-app-layout>
