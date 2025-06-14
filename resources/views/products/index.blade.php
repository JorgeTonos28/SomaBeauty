<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Productos del Kiosko') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('success') }}
            </div>
        @endif

        @if (auth()->user()->role === 'admin')
            <div class="mb-4">
                <a href="{{ route('products.create') }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Nuevo Producto
                </a>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">Nombre</th>
                        <th class="px-4 py-2 border">DescripciÃ³n</th>
                        <th class="px-4 py-2 border">Precio</th>
                        <th class="px-4 py-2 border">Stock</th>
                        <th class="px-4 py-2 border">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2">{{ $product->description }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($product->price, 2) }}</td>
                            <td class="px-4 py-2">{{ $product->stock }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                @if (auth()->user()->role === 'admin')
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="text-yellow-600 hover:underline">Editar</a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          onsubmit="return confirm('Â¿Eliminar este producto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('inventory.create', ['product_id' => $product->id]) }}" class="text-blue-600 hover:underline">Entrada</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
