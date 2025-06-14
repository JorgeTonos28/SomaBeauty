<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Productos del Kiosko') }}
        </h2>
    </x-slot>

    <div x-data="{selected: null}" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('success') }}
            </div>
        @endif

        @if (auth()->user()->role === 'admin')
            <div class="mb-4 flex items-center gap-4">
                <a href="{{ route('products.create') }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Nuevo Producto
                </a>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'edit-' + selected)" class="text-yellow-600" title="Editar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.1 2.1 0 113 3L6.75 19.5H3v-3.75L16.862 3.487z" />
                    </svg>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'delete-' + selected)" class="text-red-600" title="Eliminar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'entry-' + selected)" class="text-blue-600" title="Entrada">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">Nombre</th>
                        <th class="px-4 py-2 border">Precio</th>
                        <th class="px-4 py-2 border">Stock</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-t cursor-pointer" x-on:click="selected = {{ $product->id }}" :class="selected === {{ $product->id }} ? 'bg-blue-100' : ''">
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($product->price, 2) }}</td>
                            <td class="px-4 py-2">{{ $product->stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
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
        @endforeach
    </div>
</x-app-layout>
