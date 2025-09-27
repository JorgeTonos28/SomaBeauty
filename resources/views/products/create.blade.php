<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agregar Producto') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow sm:rounded-lg">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" required class="form-input w-full">
            </div>

            <div>
                <label for="price" class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                <input type="number" step="0.01" name="price" required class="form-input w-full">
            </div>

            <div>
                <label for="stock" class="block font-medium text-sm text-gray-700">Stock Inicial</label>
                <input type="number" name="stock" required class="form-input w-full">
            </div>

            <div>
                <label for="low_stock_threshold" class="block font-medium text-sm text-gray-700">Aviso de escasez</label>
                <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold') }}" class="form-input w-full" placeholder="Opcional">
                <p class="mt-1 text-xs text-gray-500">El sistema avisará cuando el stock llegue a este mínimo.</p>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>
                <x-secondary-button type="button" onclick="window.location='{{ route('products.index') }}'">Cancelar</x-secondary-button>
            </div>
        </form>
        </div>
    </div>
</x-app-layout>
