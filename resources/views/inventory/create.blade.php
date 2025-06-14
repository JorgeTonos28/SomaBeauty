<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Entrada de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-6">
            @csrf
            <div>
                <label for="product_id" class="block font-medium text-sm text-gray-700">Producto</label>
                <select name="product_id" class="form-input w-full">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity" class="block font-medium text-sm text-gray-700">Cantidad</label>
                <input type="number" name="quantity" min="1" required class="form-input w-full">
            </div>
            <div>
                <label for="description" class="block font-medium text-sm text-gray-700">Descripción</label>
                <input type="text" name="description" class="form-input w-full">
            </div>
            <div class="flex items-center gap-4">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Guardar</button>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
