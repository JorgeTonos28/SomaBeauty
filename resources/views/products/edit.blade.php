<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Producto') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ $product->name }}" required class="form-input w-full">
            </div>

            <div>
                <label for="price" class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                <input type="number" step="0.01" name="price" value="{{ $product->price }}" required class="form-input w-full">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Stock actual</label>
                <input type="number" value="{{ $product->stock }}" disabled class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label for="low_stock_threshold" class="block font-medium text-sm text-gray-700">Aviso de escasez</label>
                <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" class="form-input w-full" placeholder="Opcional">
                <p class="mt-1 text-xs text-gray-500">
                    Establece el stock mínimo antes de mostrar alertas.
                    Si lo dejas vacío usaremos el valor general de {{ $defaultMinimumStock }} unidades.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Actualizar
                </button>
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
